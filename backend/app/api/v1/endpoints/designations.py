"""
Designation management endpoints
"""

from typing import Any
from fastapi import APIRouter, Depends

from app.api import deps

router = APIRouter()


@router.get("/")
async def get_designations(
    current_user: dict = Depends(deps.get_current_user_with_permission("employees:read"))
) -> Any:
    """
    Get list of designations
    """
    # TODO: Implement designation listing
    return {"message": "Designation endpoints - to be implemented"}