"""
Reports endpoints
"""

from typing import Any
from datetime import date, datetime
from fastapi import APIRouter, Depends, Query
from fastapi.responses import StreamingResponse
from sqlalchemy.orm import Session
from sqlalchemy import func, extract
import io
import csv
from decimal import Decimal

from app.api import deps
from app.models.user import User
from app.models.attendance import Attendance
from app.models.leave import Leave
from app.models.payroll import Payroll
from app.models.payment import Payment
from app.models.salary_structure import SalaryStructure

router = APIRouter()


@router.get("/payroll-summary")
async def get_payroll_summary(
    month: int = Query(..., ge=1, le=12),
    year: int = Query(...),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("reports:read"))
) -> Any:
    """
    Get payroll summary for a specific month/year
    """
    payrolls = db.query(Payroll).filter(
        Payroll.month == month,
        Payroll.year == year
    ).all()
    
    total_gross = sum([p.gross_salary for p in payrolls])
    total_deductions = sum([p.total_deductions for p in payrolls])
    total_net = sum([p.net_salary for p in payrolls])
    
    return {
        "month": month,
        "year": year,
        "total_employees": len(payrolls),
        "total_gross_salary": float(total_gross),
        "total_deductions": float(total_deductions),
        "total_net_salary": float(total_net),
        "payrolls": [
            {
                "employee_id": p.employee_id,
                "gross_salary": float(p.gross_salary),
                "net_salary": float(p.net_salary),
                "status": p.get_status_name()
            }
            for p in payrolls
        ]
    }


@router.get("/attendance-summary")
async def get_attendance_summary(
    start_date: date = Query(...),
    end_date: date = Query(...),
    employee_id: int = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("reports:read"))
) -> Any:
    """
    Get attendance summary for a date range
    """
    query = db.query(Attendance).filter(
        Attendance.date >= start_date,
        Attendance.date <= end_date
    )
    
    if employee_id:
        query = query.filter(Attendance.employee_id == employee_id)
    
    records = query.all()
    
    status_counts = {
        "present": len([r for r in records if r.status == 1]),
        "absent": len([r for r in records if r.status == 2]),
        "late": len([r for r in records if r.status == 3]),
        "half_day": len([r for r in records if r.status == 4]),
        "leave": len([r for r in records if r.status == 5])
    }
    
    return {
        "start_date": start_date,
        "end_date": end_date,
        "total_records": len(records),
        "status_breakdown": status_counts
    }


@router.get("/leave-summary")
async def get_leave_summary(
    year: int = Query(...),
    employee_id: int = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("reports:read"))
) -> Any:
    """
    Get leave summary for a year
    """
    query = db.query(Leave).filter(
        extract('year', Leave.start_date) == year
    )
    
    if employee_id:
        query = query.filter(Leave.employee_id == employee_id)
    
    leaves = query.all()
    
    leave_type_counts = {}
    for leave in leaves:
        leave_type = leave.get_leave_type_name()
        if leave_type not in leave_type_counts:
            leave_type_counts[leave_type] = {"count": 0, "days": 0}
        leave_type_counts[leave_type]["count"] += 1
        leave_type_counts[leave_type]["days"] += leave.days
    
    status_counts = {
        "pending": len([l for l in leaves if l.status == 1]),
        "approved": len([l for l in leaves if l.status == 2]),
        "rejected": len([l for l in leaves if l.status == 3]),
        "cancelled": len([l for l in leaves if l.status == 4])
    }
    
    return {
        "year": year,
        "total_requests": len(leaves),
        "total_days": sum([l.days for l in leaves]),
        "leave_types": leave_type_counts,
        "status_breakdown": status_counts
    }


@router.get("/employee-statistics")
async def get_employee_statistics(
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("reports:read"))
) -> Any:
    """
    Get overall employee statistics
    """
    total_employees = db.query(User).filter(User.deletion_status == 0).count()
    active_employees = db.query(User).filter(
        User.deletion_status == 0,
        User.activation_status == 1
    ).count()
    inactive_employees = total_employees - active_employees
    
    # Count by role
    role_counts = {}
    for role_num in [1, 2, 3, 4, 5]:
        count = db.query(User).filter(
            User.deletion_status == 0,
            User.access_label == role_num
        ).count()
        role_names = {1: "superadmin", 2: "admin", 3: "hr", 4: "manager", 5: "employee"}
        role_counts[role_names[role_num]] = count
    
    return {
        "total_employees": total_employees,
        "active_employees": active_employees,
        "inactive_employees": inactive_employees,
        "by_role": role_counts
    }


@router.get("/employee-detailed")
async def get_detailed_employee_report(
    employee_id: int = Query(None),
    start_date: date = Query(None),
    end_date: date = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("reports:read"))
) -> Any:
    """
    Get detailed employee report including attendance, leave, and payroll
    """
    query = db.query(User).filter(User.deletion_status == 0)
    
    if employee_id:
        query = query.filter(User.id == employee_id)
    
    employees = query.all()
    
    report_data = []
    for emp in employees:
        # Attendance data
        attendance_query = db.query(Attendance).filter(Attendance.employee_id == emp.id)
        if start_date:
            attendance_query = attendance_query.filter(Attendance.date >= start_date)
        if end_date:
            attendance_query = attendance_query.filter(Attendance.date <= end_date)
        attendance_records = attendance_query.all()
        
        # Leave data
        leave_query = db.query(Leave).filter(Leave.employee_id == emp.id)
        if start_date:
            leave_query = leave_query.filter(Leave.start_date >= start_date)
        if end_date:
            leave_query = leave_query.filter(Leave.end_date <= end_date)
        leave_records = leave_query.all()
        
        # Payroll data
        payroll_query = db.query(Payroll).filter(Payroll.employee_id == emp.id)
        if start_date and end_date:
            payroll_query = payroll_query.filter(
                Payroll.year >= start_date.year,
                Payroll.year <= end_date.year
            )
        payroll_records = payroll_query.all()
        
        report_data.append({
            "employee_id": emp.id,
            "employee_name": emp.name,
            "employee_number": emp.employee_id,
            "designation": emp.designation.name if emp.designation else None,
            "attendance": {
                "total_days": len(attendance_records),
                "present": len([r for r in attendance_records if r.status == 1]),
                "absent": len([r for r in attendance_records if r.status == 2]),
                "late": len([r for r in attendance_records if r.status == 3]),
                "half_day": len([r for r in attendance_records if r.status == 4])
            },
            "leave": {
                "total_requests": len(leave_records),
                "total_days": sum([l.days for l in leave_records]),
                "approved": len([l for l in leave_records if l.status == 2]),
                "pending": len([l for l in leave_records if l.status == 1])
            },
            "payroll": {
                "total_records": len(payroll_records),
                "total_gross": float(sum([p.gross_salary for p in payroll_records])),
                "total_net": float(sum([p.net_salary for p in payroll_records]))
            }
        })
    
    return {
        "start_date": start_date,
        "end_date": end_date,
        "employees": report_data
    }


@router.get("/payroll-detailed")
async def get_detailed_payroll_report(
    month: int = Query(None, ge=1, le=12),
    year: int = Query(None),
    employee_id: int = Query(None),
    frequency: str = Query(None, pattern="^(daily|weekly|bi-weekly|monthly|yearly)$"),
    week_number: int = Query(None, ge=1, le=53),
    start_date: date = Query(None),
    end_date: date = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("reports:read"))
) -> Any:
    """
    Get detailed payroll report with breakdown of all components
    Supports daily, weekly, bi-weekly, monthly, and yearly frequency filtering
    """
    query = db.query(Payroll)
    
    if employee_id:
        query = query.filter(Payroll.employee_id == employee_id)
    
    if month:
        query = query.filter(Payroll.month == month)
    
    if year:
        query = query.filter(Payroll.year == year)
    
    if frequency:
        query = query.filter(Payroll.payment_frequency == frequency)
    
    if week_number:
        query = query.filter(Payroll.week_number == week_number)
    
    # Date range filtering
    if start_date:
        query = query.filter(
            (Payroll.pay_period_start >= start_date) | 
            (Payroll.pay_period_start.is_(None))
        )
    
    if end_date:
        query = query.filter(
            (Payroll.pay_period_end <= end_date) | 
            (Payroll.pay_period_end.is_(None))
        )
    
    payrolls = query.order_by(Payroll.year.desc(), Payroll.month.desc().nullslast()).all()
    
    detailed_records = []
    for p in payrolls:
        detailed_records.append({
            "employee_id": p.employee_id,
            "employee_name": p.employee.name if p.employee else None,
            "payment_frequency": p.payment_frequency,
            "period_label": p.get_period_label(),
            "month": p.month,
            "year": p.year,
            "week_number": p.week_number,
            "pay_period_start": p.pay_period_start,
            "pay_period_end": p.pay_period_end,
            "basic_salary": float(p.basic_salary),
            "allowances": {
                "house": float(p.house_allowance or 0),
                "transport": float(p.transport_allowance or 0),
                "medical": float(p.medical_allowance or 0),
                "other": float(p.other_allowances or 0)
            },
            "overtime": {
                "hours": float(p.overtime_hours or 0),
                "amount": float(p.overtime_amount or 0)
            },
            "rates": {
                "daily": float(p.daily_rate or 0),
                "hourly": float(p.hourly_rate or 0)
            },
            "work_info": {
                "working_days": p.working_days,
                "days_worked": p.days_worked,
                "hours_worked": float(p.hours_worked or 0)
            },
            "gross_salary": float(p.gross_salary),
            "deductions": {
                "nssf": float(p.nssf or 0),
                "nhif": float(p.nhif or 0),
                "paye": float(p.paye or 0),
                "loan": float(p.loan_deduction or 0),
                "other": float(p.other_deductions or 0)
            },
            "total_deductions": float(p.total_deductions),
            "net_salary": float(p.net_salary),
            "status": p.get_status_name(),
            "payment_date": p.payment_date,
            "payment_method": p.payment_method
        })
    
    # Calculate totals
    total_gross = sum([p.gross_salary for p in payrolls])
    total_deductions = sum([p.total_deductions for p in payrolls])
    total_net = sum([p.net_salary for p in payrolls])
    
    # Group by frequency for summary
    by_frequency = {}
    for p in payrolls:
        freq = p.payment_frequency or "monthly"
        if freq not in by_frequency:
            by_frequency[freq] = {"count": 0, "gross": 0, "net": 0}
        by_frequency[freq]["count"] += 1
        by_frequency[freq]["gross"] += float(p.gross_salary)
        by_frequency[freq]["net"] += float(p.net_salary)
    
    # Group by status
    by_status = {}
    for p in payrolls:
        status = p.get_status_name()
        if status not in by_status:
            by_status[status] = {"count": 0, "amount": 0}
        by_status[status]["count"] += 1
        by_status[status]["amount"] += float(p.net_salary)
    
    return {
        "filters": {
            "frequency": frequency,
            "month": month,
            "year": year,
            "week_number": week_number,
            "start_date": start_date,
            "end_date": end_date
        },
        "total_employees": len(payrolls),
        "summary": {
            "total_gross_salary": float(total_gross),
            "total_deductions": float(total_deductions),
            "total_net_salary": float(total_net)
        },
        "by_frequency": by_frequency,
        "by_status": by_status,
        "records": detailed_records
    }


@router.get("/tax-report")
async def get_tax_report(
    month: int = Query(None, ge=1, le=12),
    year: int = Query(...),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("reports:read"))
) -> Any:
    """
    Get tax report (PAYE, NSSF, NHIF)
    """
    query = db.query(Payroll).filter(Payroll.year == year)
    
    if month:
        query = query.filter(Payroll.month == month)
    
    payrolls = query.all()
    
    tax_records = []
    for p in payrolls:
        tax_records.append({
            "employee_id": p.employee_id,
            "employee_name": p.employee.name if p.employee else None,
            "kra_pin": p.employee.kra_pin if p.employee else None,
            "nssf_no": p.employee.nssf_no if p.employee else None,
            "nhif_no": p.employee.nhif_no if p.employee else None,
            "month": p.month,
            "year": p.year,
            "gross_salary": float(p.gross_salary),
            "nssf": float(p.nssf),
            "nhif": float(p.nhif),
            "paye": float(p.paye),
            "total_tax": float(p.nssf + p.nhif + p.paye)
        })
    
    # Calculate totals
    total_nssf = sum([p.nssf for p in payrolls])
    total_nhif = sum([p.nhif for p in payrolls])
    total_paye = sum([p.paye for p in payrolls])
    
    return {
        "month": month,
        "year": year,
        "total_employees": len(payrolls),
        "summary": {
            "total_nssf": float(total_nssf),
            "total_nhif": float(total_nhif),
            "total_paye": float(total_paye),
            "total_tax": float(total_nssf + total_nhif + total_paye)
        },
        "records": tax_records
    }


@router.get("/p9-report")
async def get_p9_report(
    year: int = Query(...),
    employee_id: int = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("reports:read"))
) -> Any:
    """
    Get P9 Tax Deduction Card report (annual tax report for Kenya)
    """
    query = db.query(Payroll).filter(Payroll.year == year)
    
    if employee_id:
        query = query.filter(Payroll.employee_id == employee_id)
    
    payrolls = query.all()
    
    # Group by employee
    employee_data = {}
    for p in payrolls:
        if p.employee_id not in employee_data:
            employee_data[p.employee_id] = {
                "employee_id": p.employee_id,
                "employee_name": p.employee.name if p.employee else None,
                "employee_number": p.employee.employee_id if p.employee else None,
                "kra_pin": p.employee.kra_pin if p.employee else None,
                "nssf_no": p.employee.nssf_no if p.employee else None,
                "nhif_no": p.employee.nhif_no if p.employee else None,
                "monthly_data": [],
                "totals": {
                    "gross_salary": 0,
                    "nssf": 0,
                    "nhif": 0,
                    "paye": 0,
                    "net_salary": 0
                }
            }
        
        employee_data[p.employee_id]["monthly_data"].append({
            "month": p.month,
            "gross_salary": float(p.gross_salary),
            "nssf": float(p.nssf),
            "nhif": float(p.nhif),
            "paye": float(p.paye),
            "net_salary": float(p.net_salary)
        })
        
        employee_data[p.employee_id]["totals"]["gross_salary"] += float(p.gross_salary)
        employee_data[p.employee_id]["totals"]["nssf"] += float(p.nssf)
        employee_data[p.employee_id]["totals"]["nhif"] += float(p.nhif)
        employee_data[p.employee_id]["totals"]["paye"] += float(p.paye)
        employee_data[p.employee_id]["totals"]["net_salary"] += float(p.net_salary)
    
    return {
        "year": year,
        "employees": list(employee_data.values())
    }


@router.get("/leave-detailed")
async def get_detailed_leave_report(
    year: int = Query(None),
    employee_id: int = Query(None),
    leave_type: int = Query(None),
    status: int = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("reports:read"))
) -> Any:
    """
    Get detailed leave report with all leave types and balances
    """
    query = db.query(Leave)
    
    if year:
        query = query.filter(extract('year', Leave.start_date) == year)
    
    if employee_id:
        query = query.filter(Leave.employee_id == employee_id)
    
    if leave_type:
        query = query.filter(Leave.leave_type == leave_type)
    
    if status:
        query = query.filter(Leave.status == status)
    
    leaves = query.all()
    
    # Group by employee
    employee_leave_data = {}
    for leave in leaves:
        if leave.employee_id not in employee_leave_data:
            employee_leave_data[leave.employee_id] = {
                "employee_id": leave.employee_id,
                "employee_name": leave.employee.name if leave.employee else None,
                "leave_types": {},
                "total_days_taken": 0,
                "total_requests": 0
            }
        
        leave_type_name = leave.get_leave_type_name()
        if leave_type_name not in employee_leave_data[leave.employee_id]["leave_types"]:
            employee_leave_data[leave.employee_id]["leave_types"][leave_type_name] = {
                "requests": 0,
                "days": 0,
                "approved": 0,
                "pending": 0,
                "rejected": 0
            }
        
        employee_leave_data[leave.employee_id]["leave_types"][leave_type_name]["requests"] += 1
        employee_leave_data[leave.employee_id]["leave_types"][leave_type_name]["days"] += leave.days
        
        if leave.status == 2:
            employee_leave_data[leave.employee_id]["leave_types"][leave_type_name]["approved"] += 1
        elif leave.status == 1:
            employee_leave_data[leave.employee_id]["leave_types"][leave_type_name]["pending"] += 1
        elif leave.status == 3:
            employee_leave_data[leave.employee_id]["leave_types"][leave_type_name]["rejected"] += 1
        
        employee_leave_data[leave.employee_id]["total_days_taken"] += leave.days
        employee_leave_data[leave.employee_id]["total_requests"] += 1
    
    return {
        "year": year,
        "total_employees": len(employee_leave_data),
        "employees": list(employee_leave_data.values())
    }


@router.get("/payment-report")
async def get_payment_report(
    start_date: date = Query(None),
    end_date: date = Query(None),
    employee_id: int = Query(None),
    payment_type: str = Query(None),
    payment_method: str = Query(None),
    status: int = Query(None),
    group_by: str = Query("none", pattern="^(none|day|week|month|year|employee|type|method)$"),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("reports:read"))
) -> Any:
    """
    Get comprehensive payment report with flexible grouping options
    Supports grouping by day, week, month, year, employee, type, or method
    """
    from collections import defaultdict
    from datetime import timedelta
    
    query = db.query(Payment)
    
    if start_date:
        query = query.filter(Payment.payment_date >= start_date)
    
    if end_date:
        query = query.filter(Payment.payment_date <= end_date)
    
    if employee_id:
        query = query.filter(Payment.employee_id == employee_id)
    
    if payment_type:
        query = query.filter(Payment.payment_type == payment_type)
    
    if payment_method:
        query = query.filter(Payment.payment_method == payment_method)
    
    if status:
        query = query.filter(Payment.status == status)
    
    payments = query.order_by(Payment.payment_date.desc()).all()
    
    # Individual payment records
    payment_records = []
    for p in payments:
        payment_records.append({
            "id": p.id,
            "employee_id": p.employee_id,
            "employee_name": p.employee.name if p.employee else None,
            "amount": float(p.amount),
            "payment_date": p.payment_date,
            "payment_type": p.payment_type,
            "payment_method": p.payment_method,
            "reference_number": p.reference_number,
            "status": p.get_status_name(),
            "description": p.description if hasattr(p, 'description') else None
        })
    
    total_amount = sum([p.amount for p in payments])
    
    # Calculate grouped data based on group_by parameter
    grouped_data = defaultdict(lambda: {"count": 0, "amount": 0, "employees": set()})
    
    for p in payments:
        if group_by == "day":
            key = p.payment_date.strftime("%Y-%m-%d") if p.payment_date else "Unknown"
        elif group_by == "week":
            if p.payment_date:
                week_start = p.payment_date - timedelta(days=p.payment_date.weekday())
                key = f"Week of {week_start.strftime('%Y-%m-%d')}"
            else:
                key = "Unknown"
        elif group_by == "month":
            key = p.payment_date.strftime("%B %Y") if p.payment_date else "Unknown"
        elif group_by == "year":
            key = str(p.payment_date.year) if p.payment_date else "Unknown"
        elif group_by == "employee":
            key = p.employee.name if p.employee else f"Employee #{p.employee_id}"
        elif group_by == "type":
            key = p.payment_type or "Unknown"
        elif group_by == "method":
            key = p.payment_method or "Unknown"
        else:
            key = "all"
        
        grouped_data[key]["count"] += 1
        grouped_data[key]["amount"] += float(p.amount)
        grouped_data[key]["employees"].add(p.employee_id)
    
    # Convert sets to counts for JSON serialization
    grouped_summary = []
    for key, data in grouped_data.items():
        grouped_summary.append({
            "group": key,
            "count": data["count"],
            "amount": data["amount"],
            "unique_employees": len(data["employees"])
        })
    
    # Sort grouped summary
    if group_by in ["day", "week", "month", "year"]:
        grouped_summary.sort(key=lambda x: x["group"], reverse=True)
    else:
        grouped_summary.sort(key=lambda x: x["amount"], reverse=True)
    
    # Calculate by status
    by_status = defaultdict(lambda: {"count": 0, "amount": 0})
    for p in payments:
        status_name = p.get_status_name()
        by_status[status_name]["count"] += 1
        by_status[status_name]["amount"] += float(p.amount)
    
    # Calculate by type
    by_type = defaultdict(lambda: {"count": 0, "amount": 0})
    for p in payments:
        by_type[p.payment_type or "Unknown"]["count"] += 1
        by_type[p.payment_type or "Unknown"]["amount"] += float(p.amount)
    
    # Calculate by method
    by_method = defaultdict(lambda: {"count": 0, "amount": 0})
    for p in payments:
        by_method[p.payment_method or "Unknown"]["count"] += 1
        by_method[p.payment_method or "Unknown"]["amount"] += float(p.amount)
    
    return {
        "filters": {
            "start_date": start_date,
            "end_date": end_date,
            "employee_id": employee_id,
            "payment_type": payment_type,
            "payment_method": payment_method,
            "status": status,
            "group_by": group_by
        },
        "summary": {
            "total_payments": len(payments),
            "total_amount": float(total_amount),
            "unique_employees": len(set(p.employee_id for p in payments))
        },
        "by_status": dict(by_status),
        "by_type": dict(by_type),
        "by_method": dict(by_method),
        "grouped": grouped_summary if group_by != "none" else None,
        "records": payment_records
    }