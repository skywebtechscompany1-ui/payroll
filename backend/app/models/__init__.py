"""
Database models
"""

from app.models.user import User
from app.models.department import Department
from app.models.designation import Designation
from app.models.employee_department import EmployeeDepartment

__all__ = [
    "User",
    "Department",
    "Designation",
    "EmployeeDepartment"
]