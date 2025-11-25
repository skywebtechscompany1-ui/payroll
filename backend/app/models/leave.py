"""
Leave model - SQLAlchemy ORM model
"""

from sqlalchemy import Column, Integer, String, Date, Text, SmallInteger, ForeignKey, DateTime, func
from sqlalchemy.orm import relationship

from app.core.database import Base


class Leave(Base):
    __tablename__ = "leaves"

    id = Column(Integer, primary_key=True, index=True)
    employee_id = Column(Integer, ForeignKey("users.id"), nullable=False, index=True)
    leave_type = Column(SmallInteger, nullable=False)  # 1: Sick, 2: Casual, 3: Annual, 4: Maternity, 5: Paternity, 6: Unpaid
    start_date = Column(Date, nullable=False)
    end_date = Column(Date, nullable=False)
    days = Column(Integer, nullable=False)
    reason = Column(Text, nullable=False)
    status = Column(SmallInteger, default=1)  # 1: Pending, 2: Approved, 3: Rejected, 4: Cancelled
    approved_by = Column(Integer, ForeignKey("users.id"), nullable=True)
    approval_date = Column(Date, nullable=True)
    rejection_reason = Column(Text, nullable=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    # Relationships
    employee = relationship("User", foreign_keys=[employee_id], back_populates="leave_requests")
    approver = relationship("User", foreign_keys=[approved_by])

    def get_leave_type_name(self) -> str:
        leave_types = {
            1: "Sick Leave",
            2: "Casual Leave",
            3: "Annual Leave",
            4: "Maternity Leave",
            5: "Paternity Leave",
            6: "Unpaid Leave"
        }
        return leave_types.get(self.leave_type, "Unknown")

    def get_status_name(self) -> str:
        status_names = {
            1: "Pending",
            2: "Approved",
            3: "Rejected",
            4: "Cancelled"
        }
        return status_names.get(self.status, "Unknown")

    @property
    def is_pending(self) -> bool:
        return self.status == 1

    @property
    def is_approved(self) -> bool:
        return self.status == 2

    def approve(self, approver_id: int):
        self.status = 2
        self.approved_by = approver_id
        from datetime import date
        self.approval_date = date.today()

    def reject(self, approver_id: int, reason: str):
        self.status = 3
        self.approved_by = approver_id
        self.rejection_reason = reason
        from datetime import date
        self.approval_date = date.today()

    def cancel(self):
        self.status = 4
