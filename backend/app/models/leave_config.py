"""
Leave Configuration model - SQLAlchemy ORM model for leave policies
"""

from sqlalchemy import Column, Integer, String, Boolean, DateTime, Text, Numeric
from sqlalchemy.sql import func

from app.core.database import Base


class LeaveConfig(Base):
    __tablename__ = "leave_configs"

    id = Column(Integer, primary_key=True, index=True)
    leave_type = Column(Integer, nullable=False, unique=True, index=True)  # 1: Sick, 2: Casual, 3: Annual, etc.
    leave_type_name = Column(String(50), nullable=False)
    
    # Leave allowances
    annual_days = Column(Integer, nullable=False, default=0)  # Days allowed per year
    max_consecutive_days = Column(Integer, nullable=True)  # Max consecutive days allowed
    min_days_notice = Column(Integer, default=0)  # Minimum days notice required
    
    # Carry forward settings
    can_carry_forward = Column(Boolean, default=False)
    max_carry_forward_days = Column(Integer, default=0)
    
    # Accrual settings
    is_accrued = Column(Boolean, default=False)  # Whether leave accrues monthly
    accrual_rate = Column(Numeric(5, 2), default=0)  # Days accrued per month
    
    # Eligibility
    requires_approval = Column(Boolean, default=True)
    is_paid = Column(Boolean, default=True)
    gender_specific = Column(String(1), nullable=True)  # M, F, or NULL for all
    
    # Documentation
    requires_documentation = Column(Boolean, default=False)
    documentation_after_days = Column(Integer, default=0)  # Require docs after X days
    
    # System fields
    is_active = Column(Boolean, default=True)
    description = Column(Text, nullable=True)
    
    # Timestamps
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    def get_available_days(self, used_days: int, carried_forward: int = 0) -> int:
        """Calculate available leave days"""
        total_available = self.annual_days + carried_forward
        return max(0, total_available - used_days)

    @staticmethod
    def get_leave_type_id(name: str) -> int:
        """Get leave type ID from name"""
        leave_types = {
            "sick": 1,
            "casual": 2,
            "annual": 3,
            "maternity": 4,
            "paternity": 5,
            "unpaid": 6
        }
        return leave_types.get(name.lower(), 0)
