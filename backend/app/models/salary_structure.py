"""
Salary Structure model - SQLAlchemy ORM model for employee salary management
"""

from sqlalchemy import Column, Integer, String, Numeric, DateTime, Text, ForeignKey, Boolean
from sqlalchemy.sql import func
from sqlalchemy.orm import relationship

from app.core.database import Base


class SalaryStructure(Base):
    __tablename__ = "salary_structures"

    id = Column(Integer, primary_key=True, index=True)
    employee_id = Column(Integer, ForeignKey("users.id"), nullable=False, index=True)
    
    # Salary components
    basic_salary = Column(Numeric(10, 2), nullable=False)
    house_allowance = Column(Numeric(10, 2), default=0)
    transport_allowance = Column(Numeric(10, 2), default=0)
    medical_allowance = Column(Numeric(10, 2), default=0)
    communication_allowance = Column(Numeric(10, 2), default=0)
    meal_allowance = Column(Numeric(10, 2), default=0)
    other_allowances = Column(Numeric(10, 2), default=0)
    
    # Deductions
    nssf_rate = Column(Numeric(5, 2), default=6.00)  # Percentage
    nhif_amount = Column(Numeric(10, 2), default=0)  # Fixed amount
    
    # Payment settings
    payment_frequency = Column(String(20), default="monthly")  # daily, weekly, monthly
    payment_method = Column(String(50), default="bank_transfer")
    
    # Status
    is_active = Column(Boolean, default=True)
    effective_from = Column(DateTime(timezone=True), nullable=False)
    effective_to = Column(DateTime(timezone=True), nullable=True)
    
    # Notes
    notes = Column(Text, nullable=True)
    
    # Audit fields
    created_by = Column(Integer, nullable=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    # Relationships
    employee = relationship("User")

    def calculate_gross_salary(self) -> float:
        """Calculate total gross salary"""
        return float(
            self.basic_salary +
            self.house_allowance +
            self.transport_allowance +
            self.medical_allowance +
            self.communication_allowance +
            self.meal_allowance +
            self.other_allowances
        )

    def calculate_nssf(self) -> float:
        """Calculate NSSF deduction"""
        gross = self.calculate_gross_salary()
        return float(gross * (self.nssf_rate / 100))

    def calculate_total_deductions(self) -> float:
        """Calculate total statutory deductions"""
        return float(self.calculate_nssf() + self.nhif_amount)

    def calculate_net_salary(self) -> float:
        """Calculate net salary before PAYE"""
        return self.calculate_gross_salary() - self.calculate_total_deductions()
