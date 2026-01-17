"""
Payroll management endpoints
Supports daily, weekly, bi-weekly, monthly, and yearly payment frequencies
"""

from typing import Any, Optional
from datetime import date
from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy.orm import Session
from sqlalchemy import and_, or_

from app.api import deps
from app.models.payroll import Payroll
from app.schemas.payroll import PayrollCreate, PayrollUpdate, PayrollProcess, PayrollResponse, PayrollList, PayrollSummary

router = APIRouter()


@router.get("/", response_model=PayrollList)
async def get_payroll_records(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    employee_id: Optional[int] = Query(None),
    month: Optional[int] = Query(None, ge=1, le=12),
    year: Optional[int] = Query(None),
    week_number: Optional[int] = Query(None, ge=1, le=53),
    status: Optional[int] = Query(None, ge=1, le=4),
    payment_frequency: Optional[str] = Query(None, pattern="^(daily|weekly|bi-weekly|monthly|yearly)$"),
    start_date: Optional[date] = Query(None, description="Filter by pay period start date"),
    end_date: Optional[date] = Query(None, description="Filter by pay period end date"),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:read"))
) -> Any:
    """
    Get payroll records with filters
    Supports filtering by payment frequency, date range, week number, etc.
    """
    query = db.query(Payroll)
    
    if employee_id:
        query = query.filter(Payroll.employee_id == employee_id)
    
    if month:
        query = query.filter(Payroll.month == month)
    
    if year:
        query = query.filter(Payroll.year == year)
    
    if week_number:
        query = query.filter(Payroll.week_number == week_number)
    
    if status:
        query = query.filter(Payroll.status == status)
    
    if payment_frequency:
        query = query.filter(Payroll.payment_frequency == payment_frequency)
    
    # Date range filter for pay period
    if start_date:
        query = query.filter(
            or_(
                Payroll.pay_period_start >= start_date,
                and_(Payroll.pay_period_start.is_(None), Payroll.year >= start_date.year)
            )
        )
    
    if end_date:
        query = query.filter(
            or_(
                Payroll.pay_period_end <= end_date,
                and_(Payroll.pay_period_end.is_(None), Payroll.year <= end_date.year)
            )
        )
    
    total = query.count()
    payrolls = query.order_by(
        Payroll.year.desc(), 
        Payroll.month.desc().nullslast(),
        Payroll.week_number.desc().nullslast(),
        Payroll.pay_period_start.desc().nullslast()
    ).offset(skip).limit(limit).all()
    
    return {
        "total": total,
        "items": payrolls
    }


@router.post("/", response_model=PayrollResponse)
async def create_payroll(
    payroll_in: PayrollCreate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:create"))
) -> Any:
    """
    Create payroll record with support for different payment frequencies
    """
    # Build query to check for existing payroll based on frequency
    existing_query = db.query(Payroll).filter(
        Payroll.employee_id == payroll_in.employee_id,
        Payroll.year == payroll_in.year,
        Payroll.payment_frequency == payroll_in.payment_frequency
    )
    
    # Add period-specific filters
    if payroll_in.payment_frequency == "monthly":
        existing_query = existing_query.filter(Payroll.month == payroll_in.month)
    elif payroll_in.payment_frequency in ["weekly", "bi-weekly"]:
        if payroll_in.week_number:
            existing_query = existing_query.filter(Payroll.week_number == payroll_in.week_number)
        elif payroll_in.pay_period_start:
            existing_query = existing_query.filter(Payroll.pay_period_start == payroll_in.pay_period_start)
    elif payroll_in.payment_frequency == "daily":
        if payroll_in.pay_period_start:
            existing_query = existing_query.filter(Payroll.pay_period_start == payroll_in.pay_period_start)
    
    existing = existing_query.first()
    
    if existing:
        raise HTTPException(status_code=400, detail="Payroll already exists for this period")
    
    payroll = Payroll(
        **payroll_in.model_dump(),
        created_by=current_user["id"]
    )
    
    # Calculate totals
    payroll.calculate_totals()
    
    db.add(payroll)
    db.commit()
    db.refresh(payroll)
    
    return payroll


@router.get("/{payroll_id}", response_model=PayrollResponse)
async def get_payroll(
    payroll_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:read"))
) -> Any:
    """
    Get payroll record by ID
    """
    payroll = db.query(Payroll).filter(Payroll.id == payroll_id).first()
    
    if not payroll:
        raise HTTPException(status_code=404, detail="Payroll record not found")
    
    return payroll


@router.put("/{payroll_id}", response_model=PayrollResponse)
async def update_payroll(
    payroll_id: int,
    payroll_in: PayrollUpdate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:update"))
) -> Any:
    """
    Update payroll record (only if draft)
    """
    payroll = db.query(Payroll).filter(Payroll.id == payroll_id).first()
    
    if not payroll:
        raise HTTPException(status_code=404, detail="Payroll record not found")
    
    if payroll.status != 1:
        raise HTTPException(status_code=400, detail="Can only update draft payroll")
    
    update_data = payroll_in.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(payroll, field, value)
    
    # Recalculate totals
    payroll.calculate_totals()
    
    db.commit()
    db.refresh(payroll)
    
    return payroll


@router.post("/{payroll_id}/process", response_model=PayrollResponse)
async def process_payroll(
    payroll_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:process"))
) -> Any:
    """
    Process payroll (mark as processed)
    """
    payroll = db.query(Payroll).filter(Payroll.id == payroll_id).first()
    
    if not payroll:
        raise HTTPException(status_code=404, detail="Payroll record not found")
    
    if payroll.status != 1:
        raise HTTPException(status_code=400, detail="Payroll already processed")
    
    payroll.process(current_user["id"])
    db.commit()
    db.refresh(payroll)
    
    return payroll


@router.post("/{payroll_id}/pay", response_model=PayrollResponse)
async def mark_payroll_paid(
    payroll_id: int,
    payment: PayrollProcess,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:process"))
) -> Any:
    """
    Mark payroll as paid
    """
    payroll = db.query(Payroll).filter(Payroll.id == payroll_id).first()
    
    if not payroll:
        raise HTTPException(status_code=404, detail="Payroll record not found")
    
    if payroll.status != 2:
        raise HTTPException(status_code=400, detail="Payroll must be processed first")
    
    payroll.mark_as_paid(payment.payment_method or "Bank Transfer", payment.payment_reference)
    db.commit()
    db.refresh(payroll)
    
    return payroll


@router.delete("/{payroll_id}")
async def delete_payroll(
    payroll_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:delete"))
) -> Any:
    """
    Delete payroll record (only if draft)
    """
    payroll = db.query(Payroll).filter(Payroll.id == payroll_id).first()
    
    if not payroll:
        raise HTTPException(status_code=404, detail="Payroll record not found")
    
    if payroll.status != 1:
        raise HTTPException(status_code=400, detail="Can only delete draft payroll")
    
    db.delete(payroll)
    db.commit()
    
    return {"success": True, "message": "Payroll record deleted successfully"}


@router.get("/summary/by-frequency")
async def get_payroll_summary_by_frequency(
    year: int = Query(...),
    payment_frequency: str = Query("monthly", pattern="^(daily|weekly|bi-weekly|monthly|yearly)$"),
    month: Optional[int] = Query(None, ge=1, le=12),
    week_number: Optional[int] = Query(None, ge=1, le=53),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:read"))
) -> Any:
    """
    Get payroll summary aggregated by payment frequency
    """
    from decimal import Decimal
    
    query = db.query(Payroll).filter(
        Payroll.year == year,
        Payroll.payment_frequency == payment_frequency
    )
    
    if month and payment_frequency == "monthly":
        query = query.filter(Payroll.month == month)
    
    if week_number and payment_frequency in ["weekly", "bi-weekly"]:
        query = query.filter(Payroll.week_number == week_number)
    
    payrolls = query.all()
    
    # Calculate totals
    total_gross = sum([p.gross_salary for p in payrolls], Decimal(0))
    total_deductions = sum([p.total_deductions for p in payrolls], Decimal(0))
    total_net = sum([p.net_salary for p in payrolls], Decimal(0))
    
    # Group by status
    by_status = {}
    for p in payrolls:
        status_name = p.get_status_name()
        if status_name not in by_status:
            by_status[status_name] = {"count": 0, "amount": Decimal(0)}
        by_status[status_name]["count"] += 1
        by_status[status_name]["amount"] += p.net_salary
    
    # Convert Decimal to float for JSON serialization
    for status in by_status:
        by_status[status]["amount"] = float(by_status[status]["amount"])
    
    # Generate period label
    period_label = f"{year}"
    if payment_frequency == "monthly" and month:
        months = ["", "January", "February", "March", "April", "May", "June",
                 "July", "August", "September", "October", "November", "December"]
        period_label = f"{months[month]} {year}"
    elif payment_frequency in ["weekly", "bi-weekly"] and week_number:
        period_label = f"Week {week_number}, {year}"
    
    return {
        "frequency": payment_frequency,
        "period_label": period_label,
        "total_employees": len(payrolls),
        "total_gross": float(total_gross),
        "total_deductions": float(total_deductions),
        "total_net": float(total_net),
        "by_status": by_status
    }


@router.post("/bulk-create")
async def bulk_create_payroll(
    employee_ids: list[int],
    payment_frequency: str = Query("monthly", pattern="^(daily|weekly|bi-weekly|monthly|yearly)$"),
    year: int = Query(...),
    month: Optional[int] = Query(None, ge=1, le=12),
    week_number: Optional[int] = Query(None, ge=1, le=53),
    pay_period_start: Optional[date] = Query(None),
    pay_period_end: Optional[date] = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:create"))
) -> Any:
    """
    Bulk create payroll records for multiple employees
    Uses their salary structure to populate the payroll
    """
    from app.models.salary_structure import SalaryStructure
    from app.models.user import User
    
    created_payrolls = []
    errors = []
    
    for emp_id in employee_ids:
        # Get employee's salary structure
        salary_struct = db.query(SalaryStructure).filter(
            SalaryStructure.employee_id == emp_id,
            SalaryStructure.is_active == True
        ).first()
        
        if not salary_struct:
            errors.append({"employee_id": emp_id, "error": "No active salary structure found"})
            continue
        
        # Check for existing payroll
        existing_query = db.query(Payroll).filter(
            Payroll.employee_id == emp_id,
            Payroll.year == year,
            Payroll.payment_frequency == payment_frequency
        )
        
        if payment_frequency == "monthly":
            existing_query = existing_query.filter(Payroll.month == month)
        elif payment_frequency in ["weekly", "bi-weekly"]:
            existing_query = existing_query.filter(Payroll.week_number == week_number)
        elif payment_frequency == "daily":
            existing_query = existing_query.filter(Payroll.pay_period_start == pay_period_start)
        
        if existing_query.first():
            errors.append({"employee_id": emp_id, "error": "Payroll already exists for this period"})
            continue
        
        # Calculate prorated amounts based on frequency
        monthly_basic = float(salary_struct.basic_salary)
        
        # Prorate based on frequency
        if payment_frequency == "daily":
            prorate_factor = 1 / 30
        elif payment_frequency == "weekly":
            prorate_factor = 1 / 4
        elif payment_frequency == "bi-weekly":
            prorate_factor = 1 / 2
        elif payment_frequency == "yearly":
            prorate_factor = 12
        else:  # monthly
            prorate_factor = 1
        
        # Create payroll record
        payroll = Payroll(
            employee_id=emp_id,
            year=year,
            month=month,
            week_number=week_number,
            pay_period_start=pay_period_start,
            pay_period_end=pay_period_end,
            payment_frequency=payment_frequency,
            basic_salary=monthly_basic * prorate_factor,
            house_allowance=float(salary_struct.house_allowance or 0) * prorate_factor,
            transport_allowance=float(salary_struct.transport_allowance or 0) * prorate_factor,
            medical_allowance=float(salary_struct.medical_allowance or 0) * prorate_factor,
            other_allowances=float(salary_struct.other_allowances or 0) * prorate_factor,
            daily_rate=monthly_basic / 30 if payment_frequency == "daily" else 0,
            hourly_rate=monthly_basic / 30 / 8 if payment_frequency == "daily" else 0,
            created_by=current_user["id"]
        )
        
        # Calculate totals
        payroll.calculate_totals()
        
        db.add(payroll)
        created_payrolls.append(emp_id)
    
    db.commit()
    
    return {
        "success": True,
        "created_count": len(created_payrolls),
        "created_for": created_payrolls,
        "errors": errors
    }