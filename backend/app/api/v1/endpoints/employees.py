"""
Employee management endpoints
"""

from typing import Any
from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy.orm import Session
from passlib.context import CryptContext

from app.api import deps
from app.models.user import User
from app.schemas.employee import EmployeeCreate, EmployeeUpdate, EmployeeResponse, EmployeeList

router = APIRouter()
pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")


@router.get("/", response_model=EmployeeList)
async def get_employees(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    designation_id: int = Query(None),
    status: int = Query(None, ge=0, le=1),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:read"))
) -> Any:
    """
    Get list of employees with filters
    """
    query = db.query(User).filter(User.deletion_status == 0)
    
    if designation_id:
        query = query.filter(User.designation_id == designation_id)
    
    if status is not None:
        query = query.filter(User.activation_status == status)
    
    total = query.count()
    employees = query.offset(skip).limit(limit).all()
    
    return {
        "total": total,
        "items": employees
    }


@router.post("/", response_model=EmployeeResponse)
async def create_employee(
    employee_in: EmployeeCreate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:create"))
) -> Any:
    """
    Create new employee
    """
    # Check if email already exists
    if employee_in.email:
        existing = db.query(User).filter(
            User.email == employee_in.email,
            User.deletion_status == 0
        ).first()
        if existing:
            raise HTTPException(status_code=400, detail="Email already registered")
    
    # Check if employee_id already exists
    if employee_in.employee_id:
        existing = db.query(User).filter(
            User.employee_id == employee_in.employee_id,
            User.deletion_status == 0
        ).first()
        if existing:
            raise HTTPException(status_code=400, detail="Employee ID already exists")
    
    # Hash password
    hashed_password = pwd_context.hash(employee_in.password)
    
    # Create employee
    employee_data = employee_in.dict()
    employee_data.pop("password")
    
    employee = User(
        **employee_data,
        password=hashed_password,
        created_by=current_user["id"],
        activation_status=1
    )
    
    # Set role based on access_label
    employee.role = employee.get_role_name()
    
    db.add(employee)
    db.commit()
    db.refresh(employee)
    
    return employee


@router.get("/{employee_id}", response_model=EmployeeResponse)
async def get_employee(
    employee_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:read"))
) -> Any:
    """
    Get employee by ID
    """
    employee = db.query(User).filter(
        User.id == employee_id,
        User.deletion_status == 0
    ).first()
    
    if not employee:
        raise HTTPException(status_code=404, detail="Employee not found")
    
    return employee


@router.put("/{employee_id}", response_model=EmployeeResponse)
async def update_employee(
    employee_id: int,
    employee_in: EmployeeUpdate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:update"))
) -> Any:
    """
    Update employee
    """
    employee = db.query(User).filter(
        User.id == employee_id,
        User.deletion_status == 0
    ).first()
    
    if not employee:
        raise HTTPException(status_code=404, detail="Employee not found")
    
    # Check email uniqueness if updating
    if employee_in.email and employee_in.email != employee.email:
        existing = db.query(User).filter(
            User.email == employee_in.email,
            User.deletion_status == 0
        ).first()
        if existing:
            raise HTTPException(status_code=400, detail="Email already registered")
    
    update_data = employee_in.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(employee, field, value)
    
    db.commit()
    db.refresh(employee)
    
    return employee


@router.delete("/{employee_id}")
async def delete_employee(
    employee_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:delete"))
) -> Any:
    """
    Soft delete employee
    """
    employee = db.query(User).filter(
        User.id == employee_id,
        User.deletion_status == 0
    ).first()
    
    if not employee:
        raise HTTPException(status_code=404, detail="Employee not found")
    
    employee.deletion_status = 1
    db.commit()
    
    return {"success": True, "message": "Employee deleted successfully"}