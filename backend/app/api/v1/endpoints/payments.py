"""
Payment endpoints
Supports filtering by date range, month/year, employee, type, method, and status
"""

from typing import Any, Optional
from datetime import date
from decimal import Decimal
from calendar import monthrange
from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy.orm import Session
from sqlalchemy import func, extract

from app.api import deps
from app.models.payment import Payment
from app.schemas.payment import PaymentCreate, PaymentUpdate, PaymentProcess, PaymentResponse, PaymentList, PaymentSummary

router = APIRouter()


@router.get("/", response_model=PaymentList)
async def get_payments(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    employee_id: Optional[int] = Query(None),
    payment_type: Optional[str] = Query(None),
    payment_method: Optional[str] = Query(None),
    status: Optional[int] = Query(None),
    start_date: Optional[date] = Query(None),
    end_date: Optional[date] = Query(None),
    month: Optional[int] = Query(None, ge=1, le=12, description="Filter by month (1-12)"),
    year: Optional[int] = Query(None, ge=2020, le=2100, description="Filter by year"),
    search: Optional[str] = Query(None, description="Search by employee name"),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payments:read"))
) -> Any:
    """
    Get all payments with comprehensive filters
    Supports filtering by month/year or date range
    """
    from app.models.user import User
    
    query = db.query(Payment)
    
    if employee_id:
        query = query.filter(Payment.employee_id == employee_id)
    
    if payment_type:
        query = query.filter(Payment.payment_type == payment_type)
    
    if payment_method:
        query = query.filter(Payment.payment_method == payment_method)
    
    if status:
        query = query.filter(Payment.status == status)
    
    # Handle month/year filtering by converting to date range
    if month and year:
        # Get the first and last day of the month
        _, last_day = monthrange(year, month)
        month_start = date(year, month, 1)
        month_end = date(year, month, last_day)
        query = query.filter(
            Payment.payment_date >= month_start,
            Payment.payment_date <= month_end
        )
    elif year and not month:
        # Filter by entire year
        year_start = date(year, 1, 1)
        year_end = date(year, 12, 31)
        query = query.filter(
            Payment.payment_date >= year_start,
            Payment.payment_date <= year_end
        )
    else:
        # Use explicit date range if provided
        if start_date:
            query = query.filter(Payment.payment_date >= start_date)
        
        if end_date:
            query = query.filter(Payment.payment_date <= end_date)
    
    # Search by employee name
    if search:
        query = query.join(User, Payment.employee_id == User.id).filter(
            User.name.ilike(f"%{search}%")
        )
    
    total = query.count()
    payments = query.order_by(Payment.payment_date.desc()).offset(skip).limit(limit).all()
    
    # Add status name and employee name
    for payment in payments:
        payment.status_name = payment.get_status_name()
        if payment.employee:
            payment.employee_name = payment.employee.name
    
    return {"total": total, "items": payments}


@router.get("/{payment_id}", response_model=PaymentResponse)
async def get_payment(
    payment_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payments:read"))
) -> Any:
    """Get payment by ID"""
    payment = db.query(Payment).filter(Payment.id == payment_id).first()
    
    if not payment:
        raise HTTPException(status_code=404, detail="Payment not found")
    
    payment.status_name = payment.get_status_name()
    return payment


@router.post("/", response_model=PaymentResponse, status_code=status.HTTP_201_CREATED)
async def create_payment(
    payment_in: PaymentCreate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payments:create"))
) -> Any:
    """Create new payment"""
    payment_data = payment_in.dict()
    payment_data["created_by"] = current_user["id"]
    
    payment = Payment(**payment_data)
    db.add(payment)
    db.commit()
    db.refresh(payment)
    
    payment.status_name = payment.get_status_name()
    return payment


@router.put("/{payment_id}", response_model=PaymentResponse)
async def update_payment(
    payment_id: int,
    payment_in: PaymentUpdate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payments:update"))
) -> Any:
    """Update payment"""
    payment = db.query(Payment).filter(Payment.id == payment_id).first()
    
    if not payment:
        raise HTTPException(status_code=404, detail="Payment not found")
    
    if payment.status == 2:
        raise HTTPException(status_code=400, detail="Cannot update completed payment")
    
    update_data = payment_in.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(payment, field, value)
    
    db.commit()
    db.refresh(payment)
    
    payment.status_name = payment.get_status_name()
    return payment


@router.post("/{payment_id}/complete", response_model=PaymentResponse)
async def complete_payment(
    payment_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payments:process"))
) -> Any:
    """Mark payment as completed"""
    payment = db.query(Payment).filter(Payment.id == payment_id).first()
    
    if not payment:
        raise HTTPException(status_code=404, detail="Payment not found")
    
    if payment.status == 2:
        raise HTTPException(status_code=400, detail="Payment already completed")
    
    payment.mark_completed()
    payment.processed_by = current_user["id"]
    
    db.commit()
    db.refresh(payment)
    
    payment.status_name = payment.get_status_name()
    return payment


@router.post("/{payment_id}/fail", response_model=PaymentResponse)
async def fail_payment(
    payment_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payments:process"))
) -> Any:
    """Mark payment as failed"""
    payment = db.query(Payment).filter(Payment.id == payment_id).first()
    
    if not payment:
        raise HTTPException(status_code=404, detail="Payment not found")
    
    payment.mark_failed()
    payment.processed_by = current_user["id"]
    
    db.commit()
    db.refresh(payment)
    
    payment.status_name = payment.get_status_name()
    return payment


@router.post("/{payment_id}/cancel", response_model=PaymentResponse)
async def cancel_payment(
    payment_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payments:delete"))
) -> Any:
    """Cancel payment"""
    payment = db.query(Payment).filter(Payment.id == payment_id).first()
    
    if not payment:
        raise HTTPException(status_code=404, detail="Payment not found")
    
    if payment.status == 2:
        raise HTTPException(status_code=400, detail="Cannot cancel completed payment")
    
    payment.cancel()
    db.commit()
    db.refresh(payment)
    
    payment.status_name = payment.get_status_name()
    return payment


@router.delete("/{payment_id}")
async def delete_payment(
    payment_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payments:delete"))
) -> Any:
    """Delete payment"""
    payment = db.query(Payment).filter(Payment.id == payment_id).first()
    
    if not payment:
        raise HTTPException(status_code=404, detail="Payment not found")
    
    if payment.status == 2:
        raise HTTPException(status_code=400, detail="Cannot delete completed payment")
    
    db.delete(payment)
    db.commit()
    
    return {"success": True, "message": "Payment deleted successfully"}


@router.get("/summary/stats", response_model=PaymentSummary)
async def get_payment_summary(
    start_date: Optional[date] = Query(None),
    end_date: Optional[date] = Query(None),
    employee_id: Optional[int] = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payments:read"))
) -> Any:
    """Get payment summary statistics"""
    query = db.query(Payment)
    
    if start_date:
        query = query.filter(Payment.payment_date >= start_date)
    
    if end_date:
        query = query.filter(Payment.payment_date <= end_date)
    
    if employee_id:
        query = query.filter(Payment.employee_id == employee_id)
    
    payments = query.all()
    
    total_amount = sum([p.amount for p in payments])
    
    # Group by type
    by_type = {}
    for payment in payments:
        by_type[payment.payment_type] = by_type.get(payment.payment_type, Decimal(0)) + payment.amount
    
    # Group by method
    by_method = {}
    for payment in payments:
        by_method[payment.payment_method] = by_method.get(payment.payment_method, Decimal(0)) + payment.amount
    
    # Group by status
    by_status = {}
    for payment in payments:
        status_name = payment.get_status_name()
        by_status[status_name] = by_status.get(status_name, Decimal(0)) + payment.amount
    
    return {
        "total_amount": total_amount,
        "total_payments": len(payments),
        "by_type": by_type,
        "by_method": by_method,
        "by_status": by_status
    }
