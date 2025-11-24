"""
Department model - SQLAlchemy ORM model
"""

from sqlalchemy import Column, Integer, String, Text, SmallInteger, DateTime, func
from sqlalchemy.orm import relationship

from app.core.database import Base


class Department(Base):
    __tablename__ = "departments"

    id = Column(Integer, primary_key=True, index=True)
    created_by = Column(Integer, nullable=False)
    department = Column(String(100), unique=True, nullable=False, index=True)
    department_description = Column(Text, nullable=True)
    publication_status = Column(SmallInteger, default=1)  # 0: unpublished, 1: published
    deletion_status = Column(SmallInteger, default=0)    # 0: not deleted, 1: deleted
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    # Relationships
    employees = relationship("User", secondary="employee_departments", back_populates="department")

    @property
    def is_active(self) -> bool:
        return self.publication_status == 1 and self.deletion_status == 0

    @property
    def is_deleted(self) -> bool:
        return self.deletion_status == 1

    def activate(self):
        self.publication_status = 1

    def deactivate(self):
        self.publication_status = 0

    def soft_delete(self):
        self.deletion_status = 1