"""
Dashboard endpoints
"""

from typing import Any
from fastapi import APIRouter, Depends

from app.api import deps

router = APIRouter()


@router.get("/")
async def get_dashboard_data(
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """
    Get dashboard data
    """
    return {"message": "Dashboard endpoints - to be implemented"}