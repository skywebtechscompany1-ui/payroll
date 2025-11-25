"""
Payroll schemas for request/response validation
"""

from typing import Optional
from datetime import date, datetime
from decimal import Decimal
from pydantic import BaseModel, Field


class PayrollBase(BaseModel):
    employee_id: int
    month: int = Field(..., ge=1, le=12)
    year: int = Field(..., ge=2020, le=2100)
    basic_salary: Decimal = Field(..., ge=0)


class PayrollCreate(PayrollBase):
    house_allowance: Decimal = Field(0, ge=0)
    transport_allowance: Decimal = Field(0, ge=0)
    medical_allowance: Decimal = Field(0, ge=0)
    other_allowances: Decimal = Field(0, ge=0)
    nssf: Decimal = Field(0, ge=0)
    nhif: Decimal = Field(0, ge=0)
    paye: Decimal = Field(0, ge=0)
    loan_deduction: Decimal = Field(0, ge=0)
    other_deductions: Decimal = Field(0, ge=0)
    working_days: int = Field(0, ge=0)
    days_worked: int = Field(0, ge=0)
    overtime_hours: Decimal = Field(0, ge=0)
    overtime_amount: Decimal = Field(0, ge=0)
    notes: Optional[str] = None


class PayrollUpdate(BaseModel):
    basic_salary: Optional[Decimal] = Field(None, ge=0)
    house_allowance: Optional[Decimal] = Field(None, ge=0)
    transport_allowance: Optional[Decimal] = Field(None, ge=0)
    medical_allowance: Optional[Decimal] = Field(None, ge=0)
    other_allowances: Optional[Decimal] = Field(None, ge=0)
    nssf: Optional[Decimal] = Field(None, ge=0)
    nhif: Optional[Decimal] = Field(None, ge=0)
    paye: Optional[Decimal] = Field(None, ge=0)
    loan_deduction: Optional[Decimal] = Field(None, ge=0)
    other_deductions: Optional[Decimal] = Field(None, ge=0)
    working_days: Optional[int] = Field(None, ge=0)
    days_worked: Optional[int] = Field(None, ge=0)
    overtime_hours: Optional[Decimal] = Field(None, ge=0)
    overtime_amount: Optional[Decimal] = Field(None, ge=0)
    notes: Optional[str] = None


class PayrollProcess(BaseModel):
    payment_method: Optional[str] = None
    payment_reference: Optional[str] = None


class PayrollResponse(PayrollBase):
    id: int
    house_allowance: Decimal
    transport_allowance: Decimal
    medical_allowance: Decimal
    other_allowances: Decimal
    nssf: Decimal
    nhif: Decimal
    paye: Decimal
    loan_deduction: Decimal
    other_deductions: Decimal
    gross_salary: Decimal
    total_deductions: Decimal
    net_salary: Decimal
    working_days: int
    days_worked: int
    overtime_hours: Decimal
    overtime_amount: Decimal
    status: int
    payment_date: Optional[date]
    payment_method: Optional[str]
    payment_reference: Optional[str]
    notes: Optional[str]
    created_by: Optional[int]
    processed_by: Optional[int]
    processed_at: Optional[datetime]
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class PayrollList(BaseModel):
    total: int
    items: list[PayrollResponse]
