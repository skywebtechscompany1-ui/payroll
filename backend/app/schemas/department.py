"""
Department schemas for request/response validation
"""

from typing import Optional
from datetime import datetime
from pydantic import BaseModel, Field


class DepartmentBase(BaseModel):
    department: str = Field(..., min_length=1, max_length=100)
    department_description: Optional[str] = None


class DepartmentCreate(DepartmentBase):
    pass


class DepartmentUpdate(BaseModel):
    department: Optional[str] = Field(None, min_length=1, max_length=100)
    department_description: Optional[str] = None
    publication_status: Optional[int] = Field(None, ge=0, le=1)


class DepartmentResponse(DepartmentBase):
    id: int
    created_by: int
    publication_status: int
    deletion_status: int
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class DepartmentList(BaseModel):
    total: int
    items: list[DepartmentResponse]
