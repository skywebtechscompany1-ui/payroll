"""
Activity Log schemas for API requests and responses
"""

from typing import Optional, Dict, Any
from datetime import datetime
from pydantic import BaseModel, Field


class ActivityLogBase(BaseModel):
    action: str = Field(..., min_length=1, max_length=100)
    module: str = Field(..., min_length=1, max_length=50)
    description: Optional[str] = None
    ip_address: Optional[str] = None
    user_agent: Optional[str] = None
    metadata: Optional[Dict[str, Any]] = None
    status: str = Field(default="success", pattern="^(success|failed|error)$")
    error_message: Optional[str] = None


class ActivityLogCreate(ActivityLogBase):
    user_id: int


class ActivityLogResponse(ActivityLogBase):
    id: int
    user_id: int
    created_at: datetime

    class Config:
        from_attributes = True


class ActivityLogList(BaseModel):
    total: int
    items: list[ActivityLogResponse]


class ActivityLogFilter(BaseModel):
    user_id: Optional[int] = None
    action: Optional[str] = None
    module: Optional[str] = None
    status: Optional[str] = None
    start_date: Optional[datetime] = None
    end_date: Optional[datetime] = None
