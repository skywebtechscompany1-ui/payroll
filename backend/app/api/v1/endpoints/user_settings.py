"""
User Settings endpoints
"""

from typing import Any
from fastapi import APIRouter, Depends, HTTPException, status
from sqlalchemy.orm import Session

from app.api import deps
from app.models.user_settings import UserSettings
from app.schemas.user_settings import UserSettingsUpdate, UserSettingsResponse

router = APIRouter()


@router.get("/me", response_model=UserSettingsResponse)
async def get_my_settings(
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """Get current user's settings"""
    settings = UserSettings.get_or_create(db, current_user["id"])
    return settings


@router.get("/{user_id}", response_model=UserSettingsResponse)
async def get_user_settings(
    user_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("settings:read"))
) -> Any:
    """Get user settings by user ID (admin only)"""
    settings = UserSettings.get_or_create(db, user_id)
    return settings


@router.put("/me", response_model=UserSettingsResponse)
async def update_my_settings(
    settings_in: UserSettingsUpdate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """Update current user's settings"""
    settings = UserSettings.get_or_create(db, current_user["id"])
    
    update_data = settings_in.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(settings, field, value)
    
    db.commit()
    db.refresh(settings)
    
    return settings


@router.put("/{user_id}", response_model=UserSettingsResponse)
async def update_user_settings(
    user_id: int,
    settings_in: UserSettingsUpdate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("settings:update"))
) -> Any:
    """Update user settings by user ID (admin only)"""
    settings = UserSettings.get_or_create(db, user_id)
    
    update_data = settings_in.dict(exclude_unset=True)
    for field, value in update_data.items():
        setattr(settings, field, value)
    
    db.commit()
    db.refresh(settings)
    
    return settings


@router.post("/me/reset")
async def reset_my_settings(
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """Reset current user's settings to defaults"""
    settings = db.query(UserSettings).filter(UserSettings.user_id == current_user["id"]).first()
    
    if settings:
        db.delete(settings)
        db.commit()
    
    # Create new default settings
    new_settings = UserSettings.get_or_create(db, current_user["id"])
    
    return new_settings
