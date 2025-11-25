"""
Attendance schemas for request/response validation
"""

from typing import Optional
from datetime import date, time, datetime
from pydantic import BaseModel, Field


class AttendanceBase(BaseModel):
    employee_id: int
    date: date
    status: int = Field(1, ge=1, le=5)


class AttendanceCreate(AttendanceBase):
    clock_in: Optional[time] = None
    clock_out: Optional[time] = None
    notes: Optional[str] = Field(None, max_length=500)


class AttendanceUpdate(BaseModel):
    clock_in: Optional[time] = None
    clock_out: Optional[time] = None
    status: Optional[int] = Field(None, ge=1, le=5)
    notes: Optional[str] = Field(None, max_length=500)


class AttendanceResponse(AttendanceBase):
    id: int
    clock_in: Optional[time]
    clock_out: Optional[time]
    notes: Optional[str]
    created_by: Optional[int]
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class AttendanceList(BaseModel):
    total: int
    items: list[AttendanceResponse]
