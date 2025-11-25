"""
Attendance management endpoints
"""

from typing import Any
from datetime import date
from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy.orm import Session

from app.api import deps
from app.models.attendance import Attendance
from app.schemas.attendance import AttendanceCreate, AttendanceUpdate, AttendanceResponse, AttendanceList

router = APIRouter()


@router.get("/", response_model=AttendanceList)
async def get_attendance(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    employee_id: int = Query(None),
    start_date: date = Query(None),
    end_date: date = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("attendance:read"))
) -> Any:
    """
    Get attendance records with filters
    """
    query = db.query(Attendance)
    
    if employee_id:
        query = query.filter(Attendance.employee_id == employee_id)
    
    if start_date:
        query = query.filter(Attendance.date >= start_date)
    
    if end_date:
        query = query.filter(Attendance.date <= end_date)
    
    total = query.count()
    records = query.order_by(Attendance.date.desc()).offset(skip).limit(limit).all()
    
    return {
        "total": total,
        "items": records
    }


@router.post("/", response_model=AttendanceResponse)
async def create_attendance(
    attendance_in: AttendanceCreate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("attendance:create"))
) -> Any:
    """
    Create attendance record
    """
    # Check if attendance already exists for this employee and date
    existing = db.query(Attendance).filter(
        Attendance.employee_id == attendance_in.employee_id,
        Attendance.date == attendance_in.date
    ).first()
    
    if existing:
        raise HTTPException(status_code=400, detail="Attendance already recorded for this date")
    
    attendance = Attendance(
        **attendance_in.dict(),
        created_by=current_user["id"]
    )
    db.add(attendance)
    db.commit()
    db.refresh(attendance)
    
    return attendance


@router.get("/{attendance_id}", response_model=AttendanceResponse)
async def get_attendance_record(
    attendance_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("attendance:read"))
) -> Any:
    """
    Get attendance record by ID
    """
    attendance = db.query(Attendance).filter(Attendance.id == attendance_id).first()
    
    if not attendance:
        raise HTTPException(status_code=404, detail="Attendance record not found")
    
    return attendance


@router.put("/{attendance_id}", response_model=AttendanceResponse)
async def update_attendance(
    attendance_id: int,
    attendance_in: AttendanceUpdate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("attendance:update"))
) -> Any:
    """
    Update attendance record
    """
    attendance = db.query(Attendance).filter(Attendance.id == attendance_id).first()
    
    if not attendance:
        raise HTTPException(status_code=404, detail="Attendance record not found")
    
    update_data = attendance_in.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(attendance, field, value)
    
    db.commit()
    db.refresh(attendance)
    
    return attendance


@router.delete("/{attendance_id}")
async def delete_attendance(
    attendance_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("attendance:delete"))
) -> Any:
    """
    Delete attendance record
    """
    attendance = db.query(Attendance).filter(Attendance.id == attendance_id).first()
    
    if not attendance:
        raise HTTPException(status_code=404, detail="Attendance record not found")
    
    db.delete(attendance)
    db.commit()
    
    return {"success": True, "message": "Attendance record deleted successfully"}