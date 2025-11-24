"""
Payroll management endpoints
"""

from typing import Any
from fastapi import APIRouter, Depends

from app.api import deps

router = APIRouter()


@router.get("/")
async def get_payroll_records(
    current_user: dict = Depends(deps.get_current_user_with_permission("payroll:read"))
) -> Any:
    """
    Get payroll records
    """
    return {"message": "Payroll endpoints - to be implemented"}