"""
Payroll model - SQLAlchemy ORM model
Supports daily, weekly, bi-weekly, monthly, and yearly payment frequencies
"""

from sqlalchemy import Column, Integer, String, Numeric, Date, SmallInteger, ForeignKey, DateTime, func, Text
from sqlalchemy.orm import relationship
from decimal import Decimal

from app.core.database import Base


class Payroll(Base):
    __tablename__ = "payroll"

    id = Column(Integer, primary_key=True, index=True)
    employee_id = Column(Integer, ForeignKey("users.id"), nullable=False, index=True)
    
    # Period fields
    month = Column(Integer, nullable=True)  # 1-12 (for monthly payroll)
    year = Column(Integer, nullable=False)
    week_number = Column(Integer, nullable=True)  # 1-52 (for weekly payroll)
    pay_period_start = Column(Date, nullable=True)  # Start date of pay period
    pay_period_end = Column(Date, nullable=True)  # End date of pay period
    
    # Payment frequency: daily, weekly, bi-weekly, monthly, yearly
    payment_frequency = Column(String(20), default="monthly", nullable=False)
    
    # Salary components (amounts based on frequency)
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
    working_days = Column(Integer, default=0)  # Total working days in period
    days_worked = Column(Integer, default=0)  # Actual days worked
    hours_worked = Column(Numeric(6, 2), default=0)  # For hourly/daily workers
    overtime_hours = Column(Numeric(5, 2), default=0)
    overtime_amount = Column(Numeric(10, 2), default=0)
    
    # Rate information (for daily/hourly calculations)
    daily_rate = Column(Numeric(10, 2), default=0)
    hourly_rate = Column(Numeric(10, 2), default=0)
    
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

    # Frequency multipliers for converting between different pay periods
    FREQUENCY_DAYS = {
        "daily": 1,
        "weekly": 7,
        "bi-weekly": 14,
        "monthly": 30,  # Average
        "yearly": 365
    }

    def calculate_totals(self):
        """Calculate gross, total deductions, and net salary"""
        base_salary = Decimal(str(self.basic_salary or 0))
        
        # For daily/hourly workers, calculate based on days/hours worked
        if self.payment_frequency == "daily" and self.days_worked and self.daily_rate:
            base_salary = Decimal(str(self.daily_rate)) * Decimal(str(self.days_worked))
        elif self.hours_worked and self.hourly_rate:
            base_salary = Decimal(str(self.hourly_rate)) * Decimal(str(self.hours_worked))
        
        self.gross_salary = (
            base_salary +
            Decimal(str(self.house_allowance or 0)) +
            Decimal(str(self.transport_allowance or 0)) +
            Decimal(str(self.medical_allowance or 0)) +
            Decimal(str(self.other_allowances or 0)) +
            Decimal(str(self.overtime_amount or 0))
        )
        
        self.total_deductions = (
            Decimal(str(self.nssf or 0)) +
            Decimal(str(self.nhif or 0)) +
            Decimal(str(self.paye or 0)) +
            Decimal(str(self.loan_deduction or 0)) +
            Decimal(str(self.other_deductions or 0))
        )
        
        self.net_salary = self.gross_salary - self.total_deductions

    def calculate_prorated_salary(self, monthly_salary: float, target_frequency: str = None) -> float:
        """
        Calculate prorated salary based on payment frequency
        Converts monthly salary to the target frequency
        """
        freq = target_frequency or self.payment_frequency
        monthly = Decimal(str(monthly_salary))
        
        if freq == "daily":
            return float(monthly / 30)
        elif freq == "weekly":
            return float(monthly / 4)
        elif freq == "bi-weekly":
            return float(monthly / 2)
        elif freq == "yearly":
            return float(monthly * 12)
        else:  # monthly
            return float(monthly)

    def calculate_daily_rate_from_monthly(self, monthly_salary: float) -> float:
        """Calculate daily rate from monthly salary"""
        return float(Decimal(str(monthly_salary)) / 30)

    def calculate_hourly_rate_from_monthly(self, monthly_salary: float, hours_per_day: int = 8) -> float:
        """Calculate hourly rate from monthly salary"""
        daily = self.calculate_daily_rate_from_monthly(monthly_salary)
        return float(Decimal(str(daily)) / hours_per_day)

    def get_period_label(self) -> str:
        """Get human-readable label for the pay period"""
        if self.payment_frequency == "daily":
            if self.pay_period_start:
                return self.pay_period_start.strftime("%Y-%m-%d")
            return f"{self.year}"
        elif self.payment_frequency == "weekly":
            return f"Week {self.week_number}, {self.year}"
        elif self.payment_frequency == "bi-weekly":
            if self.pay_period_start and self.pay_period_end:
                return f"{self.pay_period_start.strftime('%b %d')} - {self.pay_period_end.strftime('%b %d, %Y')}"
            return f"Week {self.week_number}-{(self.week_number or 0) + 1}, {self.year}"
        elif self.payment_frequency == "yearly":
            return f"Year {self.year}"
        else:  # monthly
            months = ["", "January", "February", "March", "April", "May", "June",
                     "July", "August", "September", "October", "November", "December"]
            return f"{months[self.month or 1]} {self.year}"

    def get_status_name(self) -> str:
        status_names = {
            1: "Draft",
            2: "Processed",
            3: "Paid",
            4: "Cancelled"
        }
        return status_names.get(self.status, "Unknown")

    def get_frequency_name(self) -> str:
        frequency_names = {
            "daily": "Daily",
            "weekly": "Weekly",
            "bi-weekly": "Bi-Weekly",
            "monthly": "Monthly",
            "yearly": "Yearly"
        }
        return frequency_names.get(self.payment_frequency, "Monthly")

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
