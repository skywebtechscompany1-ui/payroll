"""
Activity Log model - SQLAlchemy ORM model for tracking user activities
"""

from sqlalchemy import Column, Integer, String, DateTime, Text, JSON, ForeignKey
from sqlalchemy.sql import func
from sqlalchemy.orm import relationship

from app.core.database import Base


class ActivityLog(Base):
    __tablename__ = "activity_logs"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False, index=True)
    
    # Activity details
    action = Column(String(100), nullable=False, index=True)  # e.g., "login", "create_user", "update_payroll"
    module = Column(String(50), nullable=False, index=True)  # e.g., "auth", "users", "payroll"
    description = Column(Text, nullable=True)
    
    # Request details
    ip_address = Column(String(45), nullable=True)  # IPv4 or IPv6
    user_agent = Column(String(255), nullable=True)
    
    # Additional data stored as JSON
    extra_data = Column(JSON, nullable=True)  # Store additional context
    
    # Result
    status = Column(String(20), nullable=False, default="success")  # success, failed, error
    error_message = Column(Text, nullable=True)
    
    # Timestamp
    created_at = Column(DateTime(timezone=True), server_default=func.now(), index=True)

    # Relationships
    user = relationship("User")

    @staticmethod
    def log_activity(
        db,
        user_id: int,
        action: str,
        module: str,
        description: str = None,
        ip_address: str = None,
        user_agent: str = None,
        extra_data: dict = None,
        status: str = "success",
        error_message: str = None
    ):
        """Helper method to create activity log"""
        log = ActivityLog(
            user_id=user_id,
            action=action,
            module=module,
            description=description,
            ip_address=ip_address,
            user_agent=user_agent,
            extra_data=extra_data,
            status=status,
            error_message=error_message
        )
        db.add(log)
        db.commit()
        return log
