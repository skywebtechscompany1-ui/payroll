"""
User Settings schemas for API requests and responses
"""

from typing import Optional, Dict, Any
from datetime import datetime
from pydantic import BaseModel, Field


class UserSettingsBase(BaseModel):
    email_notifications: bool = True
    sms_notifications: bool = False
    push_notifications: bool = True
    notify_leave_approval: bool = True
    notify_payroll: bool = True
    notify_attendance: bool = True
    notify_announcements: bool = True
    theme: str = Field(default="light", pattern="^(light|dark|auto)$")
    language: str = Field(default="en", max_length=10)
    timezone: str = Field(default="UTC", max_length=50)
    date_format: str = Field(default="YYYY-MM-DD", max_length=20)
    dashboard_layout: Optional[Dict[str, Any]] = None
    profile_visibility: str = Field(default="all", pattern="^(all|team|private)$")
    show_email: bool = True
    show_phone: bool = True
    two_factor_enabled: bool = False
    two_factor_method: Optional[str] = Field(None, pattern="^(sms|email|app)$")
    session_timeout: int = Field(default=30, ge=5, le=1440)


class UserSettingsUpdate(BaseModel):
    email_notifications: Optional[bool] = None
    sms_notifications: Optional[bool] = None
    push_notifications: Optional[bool] = None
    notify_leave_approval: Optional[bool] = None
    notify_payroll: Optional[bool] = None
    notify_attendance: Optional[bool] = None
    notify_announcements: Optional[bool] = None
    theme: Optional[str] = Field(None, pattern="^(light|dark|auto)$")
    language: Optional[str] = Field(None, max_length=10)
    timezone: Optional[str] = Field(None, max_length=50)
    date_format: Optional[str] = Field(None, max_length=20)
    dashboard_layout: Optional[Dict[str, Any]] = None
    profile_visibility: Optional[str] = Field(None, pattern="^(all|team|private)$")
    show_email: Optional[bool] = None
    show_phone: Optional[bool] = None
    two_factor_enabled: Optional[bool] = None
    two_factor_method: Optional[str] = Field(None, pattern="^(sms|email|app)$")
    session_timeout: Optional[int] = Field(None, ge=5, le=1440)


class UserSettingsResponse(UserSettingsBase):
    id: int
    user_id: int
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True
