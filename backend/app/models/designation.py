"""
Designation model - SQLAlchemy ORM model
"""

from sqlalchemy import Column, Integer, String, Text, SmallInteger, DateTime, ForeignKey, func
from sqlalchemy.orm import relationship

from app.core.database import Base


class Designation(Base):
    __tablename__ = "designations"

    id = Column(Integer, primary_key=True, index=True)
    created_by = Column(Integer, nullable=False)
    department_id = Column(Integer, ForeignKey("departments.id"), nullable=False)
    designation = Column(String(100), unique=True, nullable=False, index=True)
    designation_description = Column(Text, nullable=True)
    publication_status = Column(SmallInteger, default=1)  # 0: unpublished, 1: published
    deletion_status = Column(SmallInteger, default=0)    # 0: not deleted, 1: deleted
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    # Relationships
    department = relationship("Department", back_populates="designations")
    employees = relationship("User", back_populates="designation")

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


# Add the back_populates relationship to Department
from app.models.department import Department
Department.designations = relationship("Designation", back_populates="department")