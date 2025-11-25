"""
Salary Structure schemas for API requests and responses
"""

from typing import Optional
from datetime import datetime
from decimal import Decimal
from pydantic import BaseModel, Field


class SalaryStructureBase(BaseModel):
    employee_id: int
    basic_salary: Decimal = Field(..., ge=0)
    house_allowance: Decimal = Field(default=0, ge=0)
    transport_allowance: Decimal = Field(default=0, ge=0)
    medical_allowance: Decimal = Field(default=0, ge=0)
    communication_allowance: Decimal = Field(default=0, ge=0)
    meal_allowance: Decimal = Field(default=0, ge=0)
    other_allowances: Decimal = Field(default=0, ge=0)
    nssf_rate: Decimal = Field(default=6.00, ge=0, le=100)
    nhif_amount: Decimal = Field(default=0, ge=0)
    payment_frequency: str = Field(default="monthly", pattern="^(daily|weekly|monthly)$")
    payment_method: str = Field(default="bank_transfer")
    is_active: bool = True
    effective_from: datetime
    effective_to: Optional[datetime] = None
    notes: Optional[str] = None


class SalaryStructureCreate(SalaryStructureBase):
    created_by: Optional[int] = None


class SalaryStructureUpdate(BaseModel):
    basic_salary: Optional[Decimal] = Field(None, ge=0)
    house_allowance: Optional[Decimal] = Field(None, ge=0)
    transport_allowance: Optional[Decimal] = Field(None, ge=0)
    medical_allowance: Optional[Decimal] = Field(None, ge=0)
    communication_allowance: Optional[Decimal] = Field(None, ge=0)
    meal_allowance: Optional[Decimal] = Field(None, ge=0)
    other_allowances: Optional[Decimal] = Field(None, ge=0)
    nssf_rate: Optional[Decimal] = Field(None, ge=0, le=100)
    nhif_amount: Optional[Decimal] = Field(None, ge=0)
    payment_frequency: Optional[str] = Field(None, pattern="^(daily|weekly|monthly)$")
    payment_method: Optional[str] = None
    is_active: Optional[bool] = None
    effective_from: Optional[datetime] = None
    effective_to: Optional[datetime] = None
    notes: Optional[str] = None


class SalaryStructureResponse(SalaryStructureBase):
    id: int
    created_by: Optional[int]
    created_at: datetime
    updated_at: datetime
    gross_salary: Optional[Decimal] = None
    total_deductions: Optional[Decimal] = None
    net_salary: Optional[Decimal] = None

    class Config:
        from_attributes = True


class SalaryStructureList(BaseModel):
    total: int
    items: list[SalaryStructureResponse]
