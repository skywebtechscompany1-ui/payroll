"""
Payslip endpoints
"""

from typing import Any, Optional
from datetime import date
from fastapi import APIRouter, Depends, Query, HTTPException
from fastapi.responses import StreamingResponse
from sqlalchemy.orm import Session
from sqlalchemy import func, extract
import io
from decimal import Decimal

from app.api import deps
from app.models.user import User
from app.models.payroll import Payroll

router = APIRouter()


@router.get("/")
async def get_payslips(
    month: Optional[int] = Query(None, ge=1, le=12),
    year: Optional[int] = Query(None),
    employee_id: Optional[int] = Query(None),
    status: Optional[int] = Query(None),
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=1000),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:read"))
) -> Any:
    """
    Get payslips with filters
    """
    query = db.query(Payroll)
    
    if month:
        query = query.filter(Payroll.month == month)
    if year:
        query = query.filter(Payroll.year == year)
    if employee_id:
        query = query.filter(Payroll.employee_id == employee_id)
    if status:
        query = query.filter(Payroll.status == status)
    
    total = query.count()
    payslips = query.offset(skip).limit(limit).all()
    
    items = []
    for payslip in payslips:
        employee = db.query(User).filter(User.id == payslip.employee_id).first()
        items.append({
            "id": payslip.id,
            "employee_id": payslip.employee_id,
            "employee_name": employee.name if employee else "Unknown",
            "employee_number": employee.employee_id if employee else "N/A",
            "month": payslip.month,
            "year": payslip.year,
            "basic_salary": float(payslip.basic_salary),
            "gross_salary": float(payslip.gross_salary),
            "total_deductions": float(payslip.total_deductions),
            "net_salary": float(payslip.net_salary),
            "status": payslip.get_status_name(),
            "payment_date": payslip.payment_date,
            "created_at": payslip.created_at
        })
    
    return {
        "items": items,
        "total": total,
        "skip": skip,
        "limit": limit
    }


@router.get("/{payslip_id}")
async def get_payslip(
    payslip_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:read"))
) -> Any:
    """
    Get single payslip details
    """
    payslip = db.query(Payroll).filter(Payroll.id == payslip_id).first()
    if not payslip:
        raise HTTPException(status_code=404, detail="Payslip not found")
    
    employee = db.query(User).filter(User.id == payslip.employee_id).first()
    
    return {
        "id": payslip.id,
        "employee": {
            "id": employee.id,
            "name": employee.name,
            "employee_number": employee.employee_id,
            "email": employee.email,
            "phone": employee.contact_no_one,
            "designation": employee.designation.name if employee.designation else None,
            "department": employee.department.name if employee.department else None,
            "bank_name": employee.bank_name,
            "account_number": employee.account_number,
            "kra_pin": employee.kra_pin,
            "nssf_no": employee.nssf_no,
            "nhif_no": employee.nhif_no
        },
        "period": {
            "month": payslip.month,
            "year": payslip.year
        },
        "earnings": {
            "basic_salary": float(payslip.basic_salary),
            "house_allowance": float(payslip.house_allowance),
            "transport_allowance": float(payslip.transport_allowance),
            "medical_allowance": float(payslip.medical_allowance),
            "other_allowances": float(payslip.other_allowances),
            "overtime_amount": float(payslip.overtime_amount),
            "gross_salary": float(payslip.gross_salary)
        },
        "deductions": {
            "nssf": float(payslip.nssf),
            "nhif": float(payslip.nhif),
            "paye": float(payslip.paye),
            "loan_deduction": float(payslip.loan_deduction),
            "other_deductions": float(payslip.other_deductions),
            "total_deductions": float(payslip.total_deductions)
        },
        "attendance": {
            "working_days": payslip.working_days,
            "days_worked": payslip.days_worked,
            "overtime_hours": float(payslip.overtime_hours)
        },
        "net_salary": float(payslip.net_salary),
        "status": payslip.get_status_name(),
        "payment_date": payslip.payment_date,
        "payment_method": payslip.payment_method,
        "payment_reference": payslip.payment_reference,
        "notes": payslip.notes,
        "created_at": payslip.created_at,
        "processed_at": payslip.processed_at
    }


@router.get("/{payslip_id}/download")
async def download_payslip_pdf(
    payslip_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:read"))
) -> Any:
    """
    Download payslip as PDF (returns payslip data for frontend PDF generation)
    """
    payslip = db.query(Payroll).filter(Payroll.id == payslip_id).first()
    if not payslip:
        raise HTTPException(status_code=404, detail="Payslip not found")
    
    employee = db.query(User).filter(User.id == payslip.employee_id).first()
    
    # Return structured data for PDF generation on frontend
    return {
        "payslip_id": payslip.id,
        "company": {
            "name": "Your Company Name",
            "address": "Company Address",
            "phone": "Company Phone",
            "email": "company@email.com"
        },
        "employee": {
            "name": employee.name,
            "employee_number": employee.employee_id,
            "designation": employee.designation.name if employee.designation else "N/A",
            "department": employee.department.name if employee.department else "N/A",
            "bank_name": employee.bank_name or "N/A",
            "account_number": employee.account_number or "N/A",
            "kra_pin": employee.kra_pin or "N/A",
            "nssf_no": employee.nssf_no or "N/A",
            "nhif_no": employee.nhif_no or "N/A"
        },
        "period": {
            "month": payslip.month,
            "year": payslip.year,
            "month_name": ["", "January", "February", "March", "April", "May", "June", 
                          "July", "August", "September", "October", "November", "December"][payslip.month]
        },
        "earnings": [
            {"description": "Basic Salary", "amount": float(payslip.basic_salary)},
            {"description": "House Allowance", "amount": float(payslip.house_allowance)},
            {"description": "Transport Allowance", "amount": float(payslip.transport_allowance)},
            {"description": "Medical Allowance", "amount": float(payslip.medical_allowance)},
            {"description": "Other Allowances", "amount": float(payslip.other_allowances)},
            {"description": "Overtime", "amount": float(payslip.overtime_amount)}
        ],
        "deductions": [
            {"description": "NSSF", "amount": float(payslip.nssf)},
            {"description": "NHIF", "amount": float(payslip.nhif)},
            {"description": "PAYE", "amount": float(payslip.paye)},
            {"description": "Loan Deduction", "amount": float(payslip.loan_deduction)},
            {"description": "Other Deductions", "amount": float(payslip.other_deductions)}
        ],
        "totals": {
            "gross_salary": float(payslip.gross_salary),
            "total_deductions": float(payslip.total_deductions),
            "net_salary": float(payslip.net_salary)
        },
        "attendance": {
            "working_days": payslip.working_days,
            "days_worked": payslip.days_worked,
            "overtime_hours": float(payslip.overtime_hours)
        },
        "payment": {
            "date": str(payslip.payment_date) if payslip.payment_date else None,
            "method": payslip.payment_method,
            "reference": payslip.payment_reference
        },
        "generated_at": str(date.today())
    }


@router.get("/bulk/download")
async def download_bulk_payslips(
    month: int = Query(..., ge=1, le=12),
    year: int = Query(...),
    employee_id: Optional[int] = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:read"))
) -> Any:
    """
    Download multiple payslips (returns array of payslip data)
    """
    query = db.query(Payroll).filter(
        Payroll.month == month,
        Payroll.year == year
    )
    
    if employee_id:
        query = query.filter(Payroll.employee_id == employee_id)
    
    payslips = query.all()
    
    result = []
    for payslip in payslips:
        employee = db.query(User).filter(User.id == payslip.employee_id).first()
        result.append({
            "payslip_id": payslip.id,
            "employee": {
                "name": employee.name,
                "employee_number": employee.employee_id,
                "designation": employee.designation.name if employee.designation else "N/A"
            },
            "period": {
                "month": payslip.month,
                "year": payslip.year
            },
            "gross_salary": float(payslip.gross_salary),
            "total_deductions": float(payslip.total_deductions),
            "net_salary": float(payslip.net_salary)
        })
    
    return {
        "payslips": result,
        "total": len(result),
        "period": f"{['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'][month]} {year}"
    }
