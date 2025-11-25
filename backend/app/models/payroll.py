"""
Payroll model - SQLAlchemy ORM model
"""

from sqlalchemy import Column, Integer, String, Numeric, Date, SmallInteger, ForeignKey, DateTime, func, Text
from sqlalchemy.orm import relationship

from app.core.database import Base


class Payroll(Base):
    __tablename__ = "payroll"

    id = Column(Integer, primary_key=True, index=True)
    employee_id = Column(Integer, ForeignKey("users.id"), nullable=False, index=True)
    month = Column(Integer, nullable=False)  # 1-12
    year = Column(Integer, nullable=False)
    
    # Salary components
    basic_salary = Column(Numeric(10, 2), nullable=False)
    house_allowance = Column(Numeric(10, 2), default=0)
    transport_allowance = Column(Numeric(10, 2), default=0)
    medical_allowance = Column(Numeric(10, 2), default=0)
    other_allowances = Column(Numeric(10, 2), default=0)
    
    # Deductions
    nssf = Column(Numeric(10, 2), default=0)
    nhif = Column(Numeric(10, 2), default=0)
    paye = Column(Numeric(10, 2), default=0)
    loan_deduction = Column(Numeric(10, 2), default=0)
    other_deductions = Column(Numeric(10, 2), default=0)
    
    # Totals
    gross_salary = Column(Numeric(10, 2), nullable=False)
    total_deductions = Column(Numeric(10, 2), nullable=False)
    net_salary = Column(Numeric(10, 2), nullable=False)
    
    # Additional info
    working_days = Column(Integer, default=0)
    days_worked = Column(Integer, default=0)
    overtime_hours = Column(Numeric(5, 2), default=0)
    overtime_amount = Column(Numeric(10, 2), default=0)
    
    # Status and payment
    status = Column(SmallInteger, default=1)  # 1: Draft, 2: Processed, 3: Paid, 4: Cancelled
    payment_date = Column(Date, nullable=True)
    payment_method = Column(String(50), nullable=True)  # Bank Transfer, Cash, Cheque
    payment_reference = Column(String(100), nullable=True)
    notes = Column(Text, nullable=True)
    
    # Audit fields
    created_by = Column(Integer, nullable=True)
    processed_by = Column(Integer, nullable=True)
    processed_at = Column(DateTime(timezone=True), nullable=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    # Relationships
    employee = relationship("User", back_populates="payroll_records")

    def calculate_totals(self):
        """Calculate gross, total deductions, and net salary"""
        self.gross_salary = (
            self.basic_salary +
            self.house_allowance +
            self.transport_allowance +
            self.medical_allowance +
            self.other_allowances +
            self.overtime_amount
        )
        
        self.total_deductions = (
            self.nssf +
            self.nhif +
            self.paye +
            self.loan_deduction +
            self.other_deductions
        )
        
        self.net_salary = self.gross_salary - self.total_deductions

    def get_status_name(self) -> str:
        status_names = {
            1: "Draft",
            2: "Processed",
            3: "Paid",
            4: "Cancelled"
        }
        return status_names.get(self.status, "Unknown")

    @property
    def is_paid(self) -> bool:
        return self.status == 3

    def mark_as_paid(self, payment_method: str, payment_reference: str = None):
        self.status = 3
        from datetime import date
        self.payment_date = date.today()
        self.payment_method = payment_method
        self.payment_reference = payment_reference

    def process(self, processor_id: int):
        self.status = 2
        self.processed_by = processor_id
        from datetime import datetime
        self.processed_at = datetime.now()
