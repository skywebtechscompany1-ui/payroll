"""
User Settings model - SQLAlchemy ORM model for user preferences and settings
"""

from sqlalchemy import Column, Integer, String, Boolean, DateTime, Text, JSON, ForeignKey
from sqlalchemy.sql import func
from sqlalchemy.orm import relationship

from app.core.database import Base


class UserSettings(Base):
    __tablename__ = "user_settings"

    id = Column(Integer, primary_key=True, index=True)
    user_id = Column(Integer, ForeignKey("users.id"), nullable=False, unique=True, index=True)
    
    # Notification preferences
    email_notifications = Column(Boolean, default=True)
    sms_notifications = Column(Boolean, default=False)
    push_notifications = Column(Boolean, default=True)
    
    # Notification types
    notify_leave_approval = Column(Boolean, default=True)
    notify_payroll = Column(Boolean, default=True)
    notify_attendance = Column(Boolean, default=True)
    notify_announcements = Column(Boolean, default=True)
    
    # Display preferences
    theme = Column(String(20), default="light")  # light, dark, auto
    language = Column(String(10), default="en")
    timezone = Column(String(50), default="UTC")
    date_format = Column(String(20), default="YYYY-MM-DD")
    
    # Dashboard preferences
    dashboard_layout = Column(JSON, nullable=True)  # Store custom dashboard layout
    
    # Privacy settings
    profile_visibility = Column(String(20), default="all")  # all, team, private
    show_email = Column(Boolean, default=True)
    show_phone = Column(Boolean, default=True)
    
    # Two-factor authentication
    two_factor_enabled = Column(Boolean, default=False)
    two_factor_method = Column(String(20), nullable=True)  # sms, email, app
    
    # Session settings
    session_timeout = Column(Integer, default=30)  # Minutes
    
    # Timestamps
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    # Relationships
    user = relationship("User")

    @staticmethod
    def get_or_create(db, user_id: int):
        """Get existing settings or create default ones"""
        settings = db.query(UserSettings).filter(UserSettings.user_id == user_id).first()
        if not settings:
            settings = UserSettings(user_id=user_id)
            db.add(settings)
            db.commit()
            db.refresh(settings)
        return settings
