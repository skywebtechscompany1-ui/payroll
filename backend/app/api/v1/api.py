"""
Main API router for v1 endpoints
"""

from fastapi import APIRouter

from app.api.v1.endpoints import auth, users, employees, departments, designations
from app.api.v1.endpoints import attendance, leave, payroll, reports, dashboard
from app.api.v1.endpoints import roles, activity_logs, leave_configs, salary_structures, payments, user_settings, payslips, system_settings, notifications

api_router = APIRouter()

# Include all endpoint routers
api_router.include_router(auth.router, prefix="/auth", tags=["Authentication"])
api_router.include_router(users.router, prefix="/users", tags=["Users"])
api_router.include_router(roles.router, prefix="/roles", tags=["Roles"])
api_router.include_router(activity_logs.router, prefix="/activity-logs", tags=["Activity Logs"])
api_router.include_router(user_settings.router, prefix="/user-settings", tags=["User Settings"])
api_router.include_router(employees.router, prefix="/employees", tags=["Employees"])
api_router.include_router(departments.router, prefix="/departments", tags=["Departments"])
api_router.include_router(designations.router, prefix="/designations", tags=["Designations"])
api_router.include_router(attendance.router, prefix="/attendance", tags=["Attendance"])
api_router.include_router(leave.router, prefix="/leave", tags=["Leave"])
api_router.include_router(leave_configs.router, prefix="/leave-configs", tags=["Leave Configuration"])
api_router.include_router(salary_structures.router, prefix="/salary-structures", tags=["Salary Structures"])
api_router.include_router(payroll.router, prefix="/payroll", tags=["Payroll"])
api_router.include_router(payments.router, prefix="/payments", tags=["Payments"])
api_router.include_router(payslips.router, prefix="/payslips", tags=["Payslips"])
api_router.include_router(reports.router, prefix="/reports", tags=["Reports"])
api_router.include_router(dashboard.router, prefix="/dashboard", tags=["Dashboard"])
api_router.include_router(system_settings.router, prefix="/system-settings", tags=["System Settings"])
api_router.include_router(notifications.router, prefix="/notifications", tags=["Notifications"])