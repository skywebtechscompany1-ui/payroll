"""
Designation management endpoints
"""

from typing import Any
from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy.orm import Session

from app.api import deps
from app.models.designation import Designation
from app.schemas.designation import DesignationCreate, DesignationUpdate, DesignationResponse, DesignationList

router = APIRouter()


@router.get("/", response_model=DesignationList)
async def get_designations(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    department_id: int = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:read"))
) -> Any:
    """
    Get list of designations with optional department filter
    """
    query = db.query(Designation).filter(Designation.deletion_status == 0)
    
    if department_id:
        query = query.filter(Designation.department_id == department_id)
    
    total = query.count()
    designations = query.offset(skip).limit(limit).all()
    
    return {
        "total": total,
        "items": designations
    }


@router.post("/", response_model=DesignationResponse)
async def create_designation(
    designation_in: DesignationCreate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:create"))
) -> Any:
    """
    Create new designation
    """
    existing = db.query(Designation).filter(
        Designation.designation == designation_in.designation,
        Designation.deletion_status == 0
    ).first()
    
    if existing:
        raise HTTPException(status_code=400, detail="Designation already exists")
    
    designation = Designation(
        **designation_in.dict(),
        created_by=current_user["id"]
    )
    db.add(designation)
    db.commit()
    db.refresh(designation)
    
    return designation


@router.get("/{designation_id}", response_model=DesignationResponse)
async def get_designation(
    designation_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:read"))
) -> Any:
    """
    Get designation by ID
    """
    designation = db.query(Designation).filter(
        Designation.id == designation_id,
        Designation.deletion_status == 0
    ).first()
    
    if not designation:
        raise HTTPException(status_code=404, detail="Designation not found")
    
    return designation


@router.put("/{designation_id}", response_model=DesignationResponse)
async def update_designation(
    designation_id: int,
    designation_in: DesignationUpdate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:update"))
) -> Any:
    """
    Update designation
    """
    designation = db.query(Designation).filter(
        Designation.id == designation_id,
        Designation.deletion_status == 0
    ).first()
    
    if not designation:
        raise HTTPException(status_code=404, detail="Designation not found")
    
    update_data = designation_in.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(designation, field, value)
    
    db.commit()
    db.refresh(designation)
    
    return designation


@router.delete("/{designation_id}")
async def delete_designation(
    designation_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:delete"))
) -> Any:
    """
    Soft delete designation
    """
    designation = db.query(Designation).filter(
        Designation.id == designation_id,
        Designation.deletion_status == 0
    ).first()
    
    if not designation:
        raise HTTPException(status_code=404, detail="Designation not found")
    
    designation.soft_delete()
    db.commit()
    
    return {"success": True, "message": "Designation deleted successfully"}