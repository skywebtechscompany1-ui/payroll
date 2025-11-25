"""
Salary Structure endpoints
"""

from typing import Any, Optional
from datetime import datetime
from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy.orm import Session

from app.api import deps
from app.models.salary_structure import SalaryStructure
from app.schemas.salary_structure import SalaryStructureCreate, SalaryStructureUpdate, SalaryStructureResponse, SalaryStructureList

router = APIRouter()


@router.get("/", response_model=SalaryStructureList)
async def get_salary_structures(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    employee_id: Optional[int] = Query(None),
    is_active: Optional[bool] = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("salary:read"))
) -> Any:
    """Get all salary structures"""
    query = db.query(SalaryStructure)
    
    if employee_id:
        query = query.filter(SalaryStructure.employee_id == employee_id)
    
    if is_active is not None:
        query = query.filter(SalaryStructure.is_active == is_active)
    
    total = query.count()
    structures = query.order_by(SalaryStructure.created_at.desc()).offset(skip).limit(limit).all()
    
    # Add calculated fields
    for structure in structures:
        structure.gross_salary = structure.calculate_gross_salary()
        structure.total_deductions = structure.calculate_total_deductions()
        structure.net_salary = structure.calculate_net_salary()
    
    return {"total": total, "items": structures}


@router.get("/{structure_id}", response_model=SalaryStructureResponse)
async def get_salary_structure(
    structure_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("salary:read"))
) -> Any:
    """Get salary structure by ID"""
    structure = db.query(SalaryStructure).filter(SalaryStructure.id == structure_id).first()
    
    if not structure:
        raise HTTPException(status_code=404, detail="Salary structure not found")
    
    # Add calculated fields
    structure.gross_salary = structure.calculate_gross_salary()
    structure.total_deductions = structure.calculate_total_deductions()
    structure.net_salary = structure.calculate_net_salary()
    
    return structure


@router.get("/employee/{employee_id}/current", response_model=SalaryStructureResponse)
async def get_current_salary_structure(
    employee_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """Get current active salary structure for employee"""
    now = datetime.now()
    
    structure = db.query(SalaryStructure).filter(
        SalaryStructure.employee_id == employee_id,
        SalaryStructure.is_active == True,
        SalaryStructure.effective_from <= now,
        (SalaryStructure.effective_to.is_(None)) | (SalaryStructure.effective_to >= now)
    ).first()
    
    if not structure:
        raise HTTPException(status_code=404, detail="No active salary structure found for this employee")
    
    # Add calculated fields
    structure.gross_salary = structure.calculate_gross_salary()
    structure.total_deductions = structure.calculate_total_deductions()
    structure.net_salary = structure.calculate_net_salary()
    
    return structure


@router.post("/", response_model=SalaryStructureResponse, status_code=status.HTTP_201_CREATED)
async def create_salary_structure(
    structure_in: SalaryStructureCreate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("salary:create"))
) -> Any:
    """Create new salary structure"""
    structure_data = structure_in.dict()
    structure_data["created_by"] = current_user["id"]
    
    structure = SalaryStructure(**structure_data)
    db.add(structure)
    db.commit()
    db.refresh(structure)
    
    # Add calculated fields
    structure.gross_salary = structure.calculate_gross_salary()
    structure.total_deductions = structure.calculate_total_deductions()
    structure.net_salary = structure.calculate_net_salary()
    
    return structure


@router.put("/{structure_id}", response_model=SalaryStructureResponse)
async def update_salary_structure(
    structure_id: int,
    structure_in: SalaryStructureUpdate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("salary:update"))
) -> Any:
    """Update salary structure"""
    structure = db.query(SalaryStructure).filter(SalaryStructure.id == structure_id).first()
    
    if not structure:
        raise HTTPException(status_code=404, detail="Salary structure not found")
    
    update_data = structure_in.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(structure, field, value)
    
    db.commit()
    db.refresh(structure)
    
    # Add calculated fields
    structure.gross_salary = structure.calculate_gross_salary()
    structure.total_deductions = structure.calculate_total_deductions()
    structure.net_salary = structure.calculate_net_salary()
    
    return structure


@router.delete("/{structure_id}")
async def delete_salary_structure(
    structure_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("salary:delete"))
) -> Any:
    """Delete salary structure"""
    structure = db.query(SalaryStructure).filter(SalaryStructure.id == structure_id).first()
    
    if not structure:
        raise HTTPException(status_code=404, detail="Salary structure not found")
    
    db.delete(structure)
    db.commit()
    
    return {"success": True, "message": "Salary structure deleted successfully"}


@router.post("/{structure_id}/deactivate")
async def deactivate_salary_structure(
    structure_id: int,
    effective_to: datetime,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("salary:update"))
) -> Any:
    """Deactivate salary structure"""
    structure = db.query(SalaryStructure).filter(SalaryStructure.id == structure_id).first()
    
    if not structure:
        raise HTTPException(status_code=404, detail="Salary structure not found")
    
    structure.is_active = False
    structure.effective_to = effective_to
    
    db.commit()
    db.refresh(structure)
    
    return structure
