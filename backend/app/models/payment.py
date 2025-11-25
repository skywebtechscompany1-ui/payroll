"""
Payment model - SQLAlchemy ORM model for tracking salary payments
"""

from sqlalchemy import Column, Integer, String, Numeric, Date, DateTime, Text, ForeignKey, SmallInteger
from sqlalchemy.sql import func
from sqlalchemy.orm import relationship

from app.core.database import Base


class Payment(Base):
    __tablename__ = "payments"

    id = Column(Integer, primary_key=True, index=True)
    employee_id = Column(Integer, ForeignKey("users.id"), nullable=False, index=True)
    payroll_id = Column(Integer, ForeignKey("payroll.id"), nullable=True, index=True)
    
    # Payment details
    amount = Column(Numeric(10, 2), nullable=False)
    payment_date = Column(Date, nullable=False, index=True)
    payment_type = Column(String(50), nullable=False)  # salary, bonus, advance, reimbursement
    payment_method = Column(String(50), nullable=False)  # bank_transfer, cash, cheque, mobile_money
    
    # Reference information
    reference_number = Column(String(100), nullable=True, unique=True)
    transaction_id = Column(String(100), nullable=True)
    
    # Bank details (if applicable)
    bank_name = Column(String(100), nullable=True)
    account_number = Column(String(50), nullable=True)
    
    # Status
    status = Column(SmallInteger, default=1)  # 1: Pending, 2: Completed, 3: Failed, 4: Cancelled
    
    # Additional info
    description = Column(Text, nullable=True)
    notes = Column(Text, nullable=True)
    
    # Audit fields
    processed_by = Column(Integer, ForeignKey("users.id"), nullable=True)
    created_by = Column(Integer, nullable=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())
    completed_at = Column(DateTime(timezone=True), nullable=True)

    # Relationships
    employee = relationship("User", foreign_keys=[employee_id])
    payroll = relationship("Payroll")
    processor = relationship("User", foreign_keys=[processed_by])

    def get_status_name(self) -> str:
        """Get human-readable status name"""
        status_names = {
            1: "Pending",
            2: "Completed",
            3: "Failed",
            4: "Cancelled"
        }
        return status_names.get(self.status, "Unknown")

    def mark_completed(self):
        """Mark payment as completed"""
        self.status = 2
        from datetime import datetime
        self.completed_at = datetime.now()

    def mark_failed(self):
        """Mark payment as failed"""
        self.status = 3

    def cancel(self):
        """Cancel payment"""
        self.status = 4

    @property
    def is_completed(self) -> bool:
        return self.status == 2
