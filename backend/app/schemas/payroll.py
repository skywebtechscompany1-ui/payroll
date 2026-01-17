"""
Payroll schemas for request/response validation
Supports daily, weekly, bi-weekly, monthly, and yearly payment frequencies
"""

from typing import Optional, Literal
from datetime import date, datetime
from decimal import Decimal
from pydantic import BaseModel, Field, model_validator


# Valid payment frequencies
PaymentFrequency = Literal["daily", "weekly", "bi-weekly", "monthly", "yearly"]


class PayrollBase(BaseModel):
    employee_id: int
    year: int = Field(..., ge=2020, le=2100)
    payment_frequency: PaymentFrequency = Field("monthly", description="Payment frequency")
    basic_salary: Decimal = Field(..., ge=0)
    
    # Optional period fields based on frequency
    month: Optional[int] = Field(None, ge=1, le=12, description="Month (1-12) for monthly payroll")
    week_number: Optional[int] = Field(None, ge=1, le=53, description="Week number (1-53) for weekly payroll")
    pay_period_start: Optional[date] = Field(None, description="Start date of pay period")
    pay_period_end: Optional[date] = Field(None, description="End date of pay period")


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
    hours_worked: Decimal = Field(0, ge=0, description="Hours worked for hourly workers")
    overtime_hours: Decimal = Field(0, ge=0)
    overtime_amount: Decimal = Field(0, ge=0)
    daily_rate: Decimal = Field(0, ge=0, description="Daily rate for daily workers")
    hourly_rate: Decimal = Field(0, ge=0, description="Hourly rate for hourly workers")
    notes: Optional[str] = None
    
    @model_validator(mode='after')
    def validate_period_fields(self):
        """Validate that appropriate period fields are set based on frequency"""
        freq = self.payment_frequency
        
        if freq == "monthly" and not self.month:
            raise ValueError("month is required for monthly payroll")
        if freq in ["weekly", "bi-weekly"] and not self.week_number and not self.pay_period_start:
            raise ValueError("week_number or pay_period_start is required for weekly/bi-weekly payroll")
        if freq == "daily" and not self.pay_period_start:
            raise ValueError("pay_period_start is required for daily payroll")
            
        return self


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
    hours_worked: Optional[Decimal] = Field(None, ge=0)
    overtime_hours: Optional[Decimal] = Field(None, ge=0)
    overtime_amount: Optional[Decimal] = Field(None, ge=0)
    daily_rate: Optional[Decimal] = Field(None, ge=0)
    hourly_rate: Optional[Decimal] = Field(None, ge=0)
    notes: Optional[str] = None


class PayrollProcess(BaseModel):
    payment_method: Optional[str] = None
    payment_reference: Optional[str] = None


class PayrollResponse(BaseModel):
    id: int
    employee_id: int
    year: int
    month: Optional[int] = None
    week_number: Optional[int] = None
    pay_period_start: Optional[date] = None
    pay_period_end: Optional[date] = None
    payment_frequency: str = "monthly"
    basic_salary: Decimal
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
    hours_worked: Optional[Decimal] = Decimal(0)
    overtime_hours: Decimal
    overtime_amount: Decimal
    daily_rate: Optional[Decimal] = Decimal(0)
    hourly_rate: Optional[Decimal] = Decimal(0)
    status: int
    payment_date: Optional[date] = None
    payment_method: Optional[str] = None
    payment_reference: Optional[str] = None
    notes: Optional[str] = None
    created_by: Optional[int] = None
    processed_by: Optional[int] = None
    processed_at: Optional[datetime] = None
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class PayrollList(BaseModel):
    total: int
    items: list[PayrollResponse]


class PayrollSummary(BaseModel):
    """Summary of payroll for a specific period"""
    frequency: str
    period_label: str
    total_employees: int
    total_gross: Decimal
    total_deductions: Decimal
    total_net: Decimal
    by_status: dict
