"""
Payment schemas for API requests and responses
"""

from typing import Optional
from datetime import date, datetime
from decimal import Decimal
from pydantic import BaseModel, Field


class PaymentBase(BaseModel):
    employee_id: int
    payroll_id: Optional[int] = None
    amount: Decimal = Field(..., ge=0)
    payment_date: date
    payment_type: str = Field(..., pattern="^(salary|bonus|advance|reimbursement)$")
    payment_method: str = Field(..., pattern="^(bank_transfer|cash|cheque|mobile_money)$")
    reference_number: Optional[str] = Field(None, max_length=100)
    transaction_id: Optional[str] = Field(None, max_length=100)
    bank_name: Optional[str] = Field(None, max_length=100)
    account_number: Optional[str] = Field(None, max_length=50)
    description: Optional[str] = None
    notes: Optional[str] = None


class PaymentCreate(PaymentBase):
    created_by: Optional[int] = None


class PaymentUpdate(BaseModel):
    amount: Optional[Decimal] = Field(None, ge=0)
    payment_date: Optional[date] = None
    payment_type: Optional[str] = Field(None, pattern="^(salary|bonus|advance|reimbursement)$")
    payment_method: Optional[str] = Field(None, pattern="^(bank_transfer|cash|cheque|mobile_money)$")
    reference_number: Optional[str] = Field(None, max_length=100)
    transaction_id: Optional[str] = Field(None, max_length=100)
    bank_name: Optional[str] = Field(None, max_length=100)
    account_number: Optional[str] = Field(None, max_length=50)
    description: Optional[str] = None
    notes: Optional[str] = None


class PaymentProcess(BaseModel):
    processed_by: int
    status: int = Field(..., ge=1, le=4)


class PaymentResponse(PaymentBase):
    id: int
    status: int
    processed_by: Optional[int]
    created_by: Optional[int]
    created_at: datetime
    updated_at: datetime
    completed_at: Optional[datetime]
    status_name: Optional[str] = None

    class Config:
        from_attributes = True


class PaymentList(BaseModel):
    total: int
    items: list[PaymentResponse]


class PaymentSummary(BaseModel):
    total_amount: Decimal
    total_payments: int
    by_type: dict
    by_method: dict
    by_status: dict
