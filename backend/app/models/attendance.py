"""
Attendance model - SQLAlchemy ORM model
"""

from sqlalchemy import Column, Integer, String, Date, Time, DateTime, SmallInteger, ForeignKey, func
from sqlalchemy.orm import relationship

from app.core.database import Base


class Attendance(Base):
    __tablename__ = "attendance"

    id = Column(Integer, primary_key=True, index=True)
    employee_id = Column(Integer, ForeignKey("users.id"), nullable=False, index=True)
    date = Column(Date, nullable=False, index=True)
    clock_in = Column(Time, nullable=True)
    clock_out = Column(Time, nullable=True)
    status = Column(SmallInteger, default=1)  # 1: Present, 2: Absent, 3: Late, 4: Half-day, 5: Leave
    notes = Column(String(500), nullable=True)
    created_by = Column(Integer, nullable=True)
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    # Relationships
    employee = relationship("User", back_populates="attendance_records")

    @property
    def hours_worked(self):
        """Calculate hours worked"""
        if self.clock_in and self.clock_out:
            from datetime import datetime, timedelta
            clock_in_dt = datetime.combine(datetime.today(), self.clock_in)
            clock_out_dt = datetime.combine(datetime.today(), self.clock_out)
            delta = clock_out_dt - clock_in_dt
            return delta.total_seconds() / 3600
        return 0

    def get_status_name(self) -> str:
        status_names = {
            1: "Present",
            2: "Absent",
            3: "Late",
            4: "Half-day",
            5: "Leave"
        }
        return status_names.get(self.status, "Unknown")
