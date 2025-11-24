"""
Attendance management endpoints
"""

from typing import Any
from fastapi import APIRouter, Depends

from app.api import deps

router = APIRouter()


@router.get("/")
async def get_attendance(
    current_user: dict = Depends(deps.get_current_user_with_permission("attendance:read"))
) -> Any:
    """
    Get attendance records
    """
    return {"message": "Attendance endpoints - to be implemented"}