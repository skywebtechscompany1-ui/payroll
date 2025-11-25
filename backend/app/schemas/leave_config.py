"""
Leave Configuration schemas for API requests and responses
"""

from typing import Optional
from datetime import datetime
from decimal import Decimal
from pydantic import BaseModel, Field


class LeaveConfigBase(BaseModel):
    leave_type: int = Field(..., ge=1, le=10)
    leave_type_name: str = Field(..., min_length=1, max_length=50)
    annual_days: int = Field(default=0, ge=0)
    max_consecutive_days: Optional[int] = Field(None, ge=1)
    min_days_notice: int = Field(default=0, ge=0)
    can_carry_forward: bool = False
    max_carry_forward_days: int = Field(default=0, ge=0)
    is_accrued: bool = False
    accrual_rate: Decimal = Field(default=0, ge=0, le=31)
    requires_approval: bool = True
    is_paid: bool = True
    gender_specific: Optional[str] = Field(None, pattern="^[MFO]$")
    requires_documentation: bool = False
    documentation_after_days: int = Field(default=0, ge=0)
    is_active: bool = True
    description: Optional[str] = None


class LeaveConfigCreate(LeaveConfigBase):
    pass


class LeaveConfigUpdate(BaseModel):
    leave_type_name: Optional[str] = Field(None, min_length=1, max_length=50)
    annual_days: Optional[int] = Field(None, ge=0)
    max_consecutive_days: Optional[int] = Field(None, ge=1)
    min_days_notice: Optional[int] = Field(None, ge=0)
    can_carry_forward: Optional[bool] = None
    max_carry_forward_days: Optional[int] = Field(None, ge=0)
    is_accrued: Optional[bool] = None
    accrual_rate: Optional[Decimal] = Field(None, ge=0, le=31)
    requires_approval: Optional[bool] = None
    is_paid: Optional[bool] = None
    gender_specific: Optional[str] = Field(None, pattern="^[MFO]$")
    requires_documentation: Optional[bool] = None
    documentation_after_days: Optional[int] = Field(None, ge=0)
    is_active: Optional[bool] = None
    description: Optional[str] = None


class LeaveConfigResponse(LeaveConfigBase):
    id: int
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class LeaveConfigList(BaseModel):
    total: int
    items: list[LeaveConfigResponse]
