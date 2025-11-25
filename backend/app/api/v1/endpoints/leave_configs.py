"""
Leave Configuration endpoints
"""

from typing import Any, Optional
from fastapi import APIRouter, Depends, HTTPException, Query, status
from sqlalchemy.orm import Session

from app.api import deps
from app.models.leave_config import LeaveConfig
from app.schemas.leave_config import LeaveConfigCreate, LeaveConfigUpdate, LeaveConfigResponse, LeaveConfigList

router = APIRouter()


@router.get("/", response_model=LeaveConfigList)
async def get_leave_configs(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    is_active: Optional[bool] = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("leave_config:read"))
) -> Any:
    """Get all leave configurations"""
    query = db.query(LeaveConfig)
    
    if is_active is not None:
        query = query.filter(LeaveConfig.is_active == is_active)
    
    total = query.count()
    configs = query.order_by(LeaveConfig.leave_type).offset(skip).limit(limit).all()
    
    return {"total": total, "items": configs}


@router.get("/{config_id}", response_model=LeaveConfigResponse)
async def get_leave_config(
    config_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("leave_config:read"))
) -> Any:
    """Get leave configuration by ID"""
    config = db.query(LeaveConfig).filter(LeaveConfig.id == config_id).first()
    
    if not config:
        raise HTTPException(status_code=404, detail="Leave configuration not found")
    
    return config


@router.get("/type/{leave_type}", response_model=LeaveConfigResponse)
async def get_leave_config_by_type(
    leave_type: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """Get leave configuration by type"""
    config = db.query(LeaveConfig).filter(LeaveConfig.leave_type == leave_type).first()
    
    if not config:
        raise HTTPException(status_code=404, detail="Leave configuration not found for this type")
    
    return config


@router.post("/", response_model=LeaveConfigResponse, status_code=status.HTTP_201_CREATED)
async def create_leave_config(
    config_in: LeaveConfigCreate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("leave_config:create"))
) -> Any:
    """Create new leave configuration"""
    # Check if config for this leave type already exists
    existing = db.query(LeaveConfig).filter(LeaveConfig.leave_type == config_in.leave_type).first()
    if existing:
        raise HTTPException(status_code=400, detail="Configuration for this leave type already exists")
    
    config = LeaveConfig(**config_in.dict())
    db.add(config)
    db.commit()
    db.refresh(config)
    
    return config


@router.put("/{config_id}", response_model=LeaveConfigResponse)
async def update_leave_config(
    config_id: int,
    config_in: LeaveConfigUpdate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("leave_config:update"))
) -> Any:
    """Update leave configuration"""
    config = db.query(LeaveConfig).filter(LeaveConfig.id == config_id).first()
    
    if not config:
        raise HTTPException(status_code=404, detail="Leave configuration not found")
    
    update_data = config_in.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(config, field, value)
    
    db.commit()
    db.refresh(config)
    
    return config


@router.delete("/{config_id}")
async def delete_leave_config(
    config_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("leave_config:delete"))
) -> Any:
    """Delete leave configuration"""
    config = db.query(LeaveConfig).filter(LeaveConfig.id == config_id).first()
    
    if not config:
        raise HTTPException(status_code=404, detail="Leave configuration not found")
    
    db.delete(config)
    db.commit()
    
    return {"success": True, "message": "Leave configuration deleted successfully"}
