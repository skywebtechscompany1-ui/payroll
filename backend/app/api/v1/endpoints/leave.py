"""
Leave management endpoints
"""

from typing import Any
from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy.orm import Session

from app.api import deps
from app.models.leave import Leave
from app.schemas.leave import LeaveCreate, LeaveUpdate, LeaveApprove, LeaveResponse, LeaveList

router = APIRouter()


@router.get("/", response_model=LeaveList)
async def get_leave_requests(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    employee_id: int = Query(None),
    status: int = Query(None, ge=1, le=4),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("leave:read"))
) -> Any:
    """
    Get leave requests with filters
    """
    query = db.query(Leave)
    
    if employee_id:
        query = query.filter(Leave.employee_id == employee_id)
    
    if status:
        query = query.filter(Leave.status == status)
    
    total = query.count()
    leaves = query.order_by(Leave.created_at.desc()).offset(skip).limit(limit).all()
    
    return {
        "total": total,
        "items": leaves
    }


@router.post("/", response_model=LeaveResponse)
async def create_leave_request(
    leave_in: LeaveCreate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("leave:create"))
) -> Any:
    """
    Create leave request
    """
    # Check for overlapping leave requests
    overlapping = db.query(Leave).filter(
        Leave.employee_id == leave_in.employee_id,
        Leave.status.in_([1, 2]),  # Pending or Approved
        Leave.start_date <= leave_in.end_date,
        Leave.end_date >= leave_in.start_date
    ).first()
    
    if overlapping:
        raise HTTPException(status_code=400, detail="Overlapping leave request exists")
    
    leave = Leave(**leave_in.dict())
    db.add(leave)
    db.commit()
    db.refresh(leave)
    
    return leave


@router.get("/{leave_id}", response_model=LeaveResponse)
async def get_leave_request(
    leave_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("leave:read"))
) -> Any:
    """
    Get leave request by ID
    """
    leave = db.query(Leave).filter(Leave.id == leave_id).first()
    
    if not leave:
        raise HTTPException(status_code=404, detail="Leave request not found")
    
    return leave


@router.put("/{leave_id}", response_model=LeaveResponse)
async def update_leave_request(
    leave_id: int,
    leave_in: LeaveUpdate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("leave:update"))
) -> Any:
    """
    Update leave request (only if pending)
    """
    leave = db.query(Leave).filter(Leave.id == leave_id).first()
    
    if not leave:
        raise HTTPException(status_code=404, detail="Leave request not found")
    
    if leave.status != 1:
        raise HTTPException(status_code=400, detail="Can only update pending leave requests")
    
    update_data = leave_in.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(leave, field, value)
    
    db.commit()
    db.refresh(leave)
    
    return leave


@router.post("/{leave_id}/approve", response_model=LeaveResponse)
async def approve_leave_request(
    leave_id: int,
    approval: LeaveApprove,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("leave:approve"))
) -> Any:
    """
    Approve or reject leave request
    """
    leave = db.query(Leave).filter(Leave.id == leave_id).first()
    
    if not leave:
        raise HTTPException(status_code=404, detail="Leave request not found")
    
    if leave.status != 1:
        raise HTTPException(status_code=400, detail="Leave request already processed")
    
    if approval.approved:
        leave.approve(current_user["id"])
    else:
        if not approval.rejection_reason:
            raise HTTPException(status_code=400, detail="Rejection reason is required")
        leave.reject(current_user["id"], approval.rejection_reason)
    
    db.commit()
    db.refresh(leave)
    
    return leave


@router.delete("/{leave_id}")
async def cancel_leave_request(
    leave_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("leave:delete"))
) -> Any:
    """
    Cancel leave request
    """
    leave = db.query(Leave).filter(Leave.id == leave_id).first()
    
    if not leave:
        raise HTTPException(status_code=404, detail="Leave request not found")
    
    if leave.status == 3:
        raise HTTPException(status_code=400, detail="Cannot cancel rejected leave")
    
    leave.cancel()
    db.commit()
    
    return {"success": True, "message": "Leave request cancelled successfully"}