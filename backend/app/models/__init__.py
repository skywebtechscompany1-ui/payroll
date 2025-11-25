"""
Database models
"""

from app.core.database import Base
from app.models.user import User
from app.models.department import Department
from app.models.designation import Designation
from app.models.employee_department import EmployeeDepartment
from app.models.attendance import Attendance
from app.models.leave import Leave
from app.models.payroll import Payroll
from app.models.role import Role
from app.models.activity_log import ActivityLog
from app.models.leave_config import LeaveConfig
from app.models.salary_structure import SalaryStructure
from app.models.payment import Payment
from app.models.user_settings import UserSettings
from app.models.notification import Notification

__all__ = [
    "Base",
    "User",
    "Department",
    "Designation",
    "EmployeeDepartment",
    "Attendance",
    "Leave",
    "Payroll",
    "Role",
    "ActivityLog",
    "LeaveConfig",
    "SalaryStructure",
    "Payment",
    "UserSettings",
    "Notification"
]