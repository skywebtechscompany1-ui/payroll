"""
Designation schemas for request/response validation
"""

from typing import Optional
from datetime import datetime
from pydantic import BaseModel, Field


class DesignationBase(BaseModel):
    designation: str = Field(..., min_length=1, max_length=100)
    department_id: int
    designation_description: Optional[str] = None


class DesignationCreate(DesignationBase):
    pass


class DesignationUpdate(BaseModel):
    designation: Optional[str] = Field(None, min_length=1, max_length=100)
    department_id: Optional[int] = None
    designation_description: Optional[str] = None
    publication_status: Optional[int] = Field(None, ge=0, le=1)


class DesignationResponse(DesignationBase):
    id: int
    created_by: int
    publication_status: int
    deletion_status: int
    created_at: datetime
    updated_at: datetime

    class Config:
        from_attributes = True


class DesignationList(BaseModel):
    total: int
    items: list[DesignationResponse]
