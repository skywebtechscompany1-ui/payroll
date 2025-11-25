"""
Leave schemas for request/response validation
"""

from typing import Optional
from datetime import date, datetime
from pydantic import BaseModel, Field


class LeaveBase(BaseModel):
    leave_type: int = Field(..., ge=1, le=6)
    start_date: date
    end_date: date
    days: int = Field(..., ge=1)
    reason: str = Field(..., min_length=10)


class LeaveCreate(LeaveBase):
    employee_id: int


class LeaveUpdate(BaseModel):
    leave_type: Optional[int] = Field(None, ge=1, le=6)
    start_date: Optional[date] = None
    end_date: Optional[date] = None
    days: Optional[int] = Field(None, ge=1)
    reason: Optional[str] = Field(None, min_length=10)


class LeaveApprove(BaseModel):
    approved: bool
    rejection_reason: Optional[str] = None


class LeaveResponse(LeaveBase):
    id: int
    employee_id: int
    status: int
    approved_by: Optional[int]
    approval_date: Optional[date]
    rejection_reason: Optional[str]
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class LeaveList(BaseModel):
    total: int
    items: list[LeaveResponse]
