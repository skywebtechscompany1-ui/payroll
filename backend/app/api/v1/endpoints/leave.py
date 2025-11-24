"""
Leave management endpoints
"""

from typing import Any
from fastapi import APIRouter, Depends

from app.api import deps

router = APIRouter()


@router.get("/")
async def get_leave_applications(
    current_user: dict = Depends(deps.get_current_user_with_permission("leave:read"))
) -> Any:
    """
    Get leave applications
    """
    return {"message": "Leave endpoints - to be implemented"}