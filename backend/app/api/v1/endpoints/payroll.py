"""
Payroll management endpoints
"""

from typing import Any
from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy.orm import Session

from app.api import deps
from app.models.payroll import Payroll
from app.schemas.payroll import PayrollCreate, PayrollUpdate, PayrollProcess, PayrollResponse, PayrollList

router = APIRouter()


@router.get("/", response_model=PayrollList)
async def get_payroll_records(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    employee_id: int = Query(None),
    month: int = Query(None, ge=1, le=12),
    year: int = Query(None),
    status: int = Query(None, ge=1, le=4),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:read"))
) -> Any:
    """
    Get payroll records with filters
    """
    query = db.query(Payroll)
    
    if employee_id:
        query = query.filter(Payroll.employee_id == employee_id)
    
    if month:
        query = query.filter(Payroll.month == month)
    
    if year:
        query = query.filter(Payroll.year == year)
    
    if status:
        query = query.filter(Payroll.status == status)
    
    total = query.count()
    payrolls = query.order_by(Payroll.year.desc(), Payroll.month.desc()).offset(skip).limit(limit).all()
    
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
    Create payroll record
    """
    # Check if payroll already exists for this employee and period
    existing = db.query(Payroll).filter(
        Payroll.employee_id == payroll_in.employee_id,
        Payroll.month == payroll_in.month,
        Payroll.year == payroll_in.year
    ).first()
    
    if existing:
        raise HTTPException(status_code=400, detail="Payroll already exists for this period")
    
    payroll = Payroll(
        **payroll_in.dict(),
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