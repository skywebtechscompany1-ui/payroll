"""
Reports management endpoints
"""

from typing import Any
from fastapi import APIRouter, Depends

from app.api import deps

router = APIRouter()


@router.get("/")
async def get_reports(
    current_user: dict = Depends(deps.get_current_user_with_permission("reports:read"))
) -> Any:
    """
    Get available reports
    """
    return {"message": "Reports endpoints - to be implemented"}