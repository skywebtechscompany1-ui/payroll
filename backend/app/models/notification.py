"""
Notification model - SQLAlchemy ORM model for user notifications
"""

from sqlalchemy import Column, Integer, String, Boolean, DateTime, Text, ForeignKey
from sqlalchemy.sql import func
from sqlalchemy.orm import relationship

from app.core.database import Base


class Notification(Base):
    __tablename__ = "notifications"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False, index=True)
    
    # Notification details
    title = Column(String(255), nullable=False)
    message = Column(Text, nullable=False)
    type = Column(String(50), nullable=False)  # success, warning, error, info
    
    # Status
    is_read = Column(Boolean, default=False, index=True)
    
    # Optional action link
    action_url = Column(String(255), nullable=True)
    action_text = Column(String(100), nullable=True)
    
    # Timestamps
    created_at = Column(DateTime(timezone=True), server_default=func.now(), index=True)
    read_at = Column(DateTime(timezone=True), nullable=True)

    # Relationships
    user = relationship("User", foreign_keys=[user_id])

    @staticmethod
    def create_notification(
        db,
        user_id: int,
        title: str,
        message: str,
        notification_type: str = "info",
        action_url: str = None,
        action_text: str = None
    ):
        """Helper method to create a notification"""
        notification = Notification(
            user_id=user_id,
            title=title,
            message=message,
            type=notification_type,
            action_url=action_url,
            action_text=action_text
        )
        db.add(notification)
        db.commit()
        db.refresh(notification)
        return notification
