"""
Employee schemas for request/response validation
"""

from typing import Optional
from datetime import date, datetime
from pydantic import BaseModel, EmailStr, Field


class EmployeeBase(BaseModel):
    name: str = Field(..., min_length=1, max_length=255)
    email: Optional[EmailStr] = None
    employee_id: Optional[str] = Field(None, max_length=20)
    designation_id: Optional[int] = None
    gender: str = Field(..., pattern="^[MFO]$")
    contact_no_one: str = Field(..., max_length=30)
    present_address: str = Field(..., max_length=255)


class EmployeeCreate(EmployeeBase):
    password: str = Field(..., min_length=6)
    access_label: int = Field(5, ge=1, le=5)  # Default to employee
    date_of_birth: Optional[date] = None
    joining_date: Optional[date] = None


class EmployeeUpdate(BaseModel):
    name: Optional[str] = Field(None, min_length=1, max_length=255)
    email: Optional[EmailStr] = None
    designation_id: Optional[int] = None
    contact_no_one: Optional[str] = Field(None, max_length=30)
    contact_no_two: Optional[str] = Field(None, max_length=30)
    present_address: Optional[str] = Field(None, max_length=255)
    permanent_address: Optional[str] = Field(None, max_length=255)
    date_of_birth: Optional[date] = None
    marital_status: Optional[int] = Field(None, ge=1, le=5)
    activation_status: Optional[int] = Field(None, ge=0, le=1)


class EmployeeResponse(EmployeeBase):
    id: int
    access_label: int
    role: Optional[str]
    activation_status: int
    deletion_status: int
    date_of_birth: Optional[date]
    joining_date: Optional[date]
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class EmployeeList(BaseModel):
    total: int
    items: list[EmployeeResponse]
