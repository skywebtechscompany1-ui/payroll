"""
Department management endpoints
"""

from typing import Any, List
from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy.orm import Session

from app.api import deps
from app.models.department import Department
from app.schemas.department import DepartmentCreate, DepartmentUpdate, DepartmentResponse, DepartmentList

router = APIRouter()


@router.get("/", response_model=DepartmentList)
async def get_departments(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:read"))
) -> Any:
    """
    Get list of departments with pagination
    """
    query = db.query(Department).filter(Department.deletion_status == 0)
    total = query.count()
    departments = query.offset(skip).limit(limit).all()
    
    return {
        "total": total,
        "items": departments
    }


@router.post("/", response_model=DepartmentResponse)
async def create_department(
    department_in: DepartmentCreate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:create"))
) -> Any:
    """
    Create new department
    """
    # Check if department already exists
    existing = db.query(Department).filter(
        Department.department == department_in.department,
        Department.deletion_status == 0
    ).first()
    
    if existing:
        raise HTTPException(status_code=400, detail="Department already exists")
    
    department = Department(
        **department_in.dict(),
        created_by=current_user["id"]
    )
    db.add(department)
    db.commit()
    db.refresh(department)
    
    return department


@router.get("/{department_id}", response_model=DepartmentResponse)
async def get_department(
    department_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:read"))
) -> Any:
    """
    Get department by ID
    """
    department = db.query(Department).filter(
        Department.id == department_id,
        Department.deletion_status == 0
    ).first()
    
    if not department:
        raise HTTPException(status_code=404, detail="Department not found")
    
    return department


@router.put("/{department_id}", response_model=DepartmentResponse)
async def update_department(
    department_id: int,
    department_in: DepartmentUpdate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:update"))
) -> Any:
    """
    Update department
    """
    department = db.query(Department).filter(
        Department.id == department_id,
        Department.deletion_status == 0
    ).first()
    
    if not department:
        raise HTTPException(status_code=404, detail="Department not found")
    
    update_data = department_in.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(department, field, value)
    
    db.commit()
    db.refresh(department)
    
    return department


@router.delete("/{department_id}")
async def delete_department(
    department_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:delete"))
) -> Any:
    """
    Soft delete department
    """
    department = db.query(Department).filter(
        Department.id == department_id,
        Department.deletion_status == 0
    ).first()
    
    if not department:
        raise HTTPException(status_code=404, detail="Department not found")
    
    department.soft_delete()
    db.commit()
    
    return {"success": True, "message": "Department deleted successfully"}