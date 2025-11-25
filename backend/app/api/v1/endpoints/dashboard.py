"""
Dashboard endpoints
"""

from typing import Any
from datetime import date, datetime, timedelta
from fastapi import APIRouter, Depends
from sqlalchemy.orm import Session
from sqlalchemy import func, extract

from app.api import deps
from app.models.user import User
from app.models.attendance import Attendance
from app.models.leave import Leave
from app.models.payroll import Payroll
from app.models.department import Department

router = APIRouter()


@router.get("/stats")
async def get_dashboard_stats(
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """
    Get dashboard statistics
    """
    # Employee statistics
    total_employees = db.query(User).filter(User.deletion_status == 0).count()
    active_employees = db.query(User).filter(
        User.deletion_status == 0,
        User.activation_status == 1
    ).count()
    
    # Today's attendance
    today = date.today()
    today_attendance = db.query(Attendance).filter(
        Attendance.date == today
    ).count()
    
    # Pending leave requests
    pending_leaves = db.query(Leave).filter(Leave.status == 1).count()
    
    # Current month payroll
    current_month = datetime.now().month
    current_year = datetime.now().year
    monthly_payroll = db.query(Payroll).filter(
        Payroll.month == current_month,
        Payroll.year == current_year
    ).all()
    
    total_monthly_payroll = sum([float(p.net_salary) for p in monthly_payroll])
    
    # Recent activity (last 7 days)
    week_ago = date.today() - timedelta(days=7)
    
    recent_employees = db.query(User).filter(
        User.created_at >= week_ago,
        User.deletion_status == 0
    ).count()
    
    recent_leaves = db.query(Leave).filter(
        Leave.created_at >= week_ago
    ).count()
    
    # Department distribution
    departments = db.query(Department).filter(
        Department.deletion_status == 0
    ).all()
    
    dept_distribution = []
    for dept in departments:
        emp_count = len(dept.employees)
        if emp_count > 0:
            dept_distribution.append({
                "name": dept.department,
                "count": emp_count
            })
    
    # Payroll trend (last 6 months)
    payroll_trend = []
    for i in range(5, -1, -1):
        target_date = datetime.now() - timedelta(days=i*30)
        month = target_date.month
        year = target_date.year
        
        month_payrolls = db.query(Payroll).filter(
            Payroll.month == month,
            Payroll.year == year
        ).all()
        
        total = sum([float(p.net_salary) for p in month_payrolls])
        payroll_trend.append({
            "month": target_date.strftime("%B"),
            "amount": total
        })
    
    # Calculate trends (compare with last month)
    last_month = (datetime.now().replace(day=1) - timedelta(days=1))
    last_month_payrolls = db.query(Payroll).filter(
        Payroll.month == last_month.month,
        Payroll.year == last_month.year
    ).all()
    last_month_total = sum([float(p.net_salary) for p in last_month_payrolls])
    
    payroll_trend_percent = 0
    if last_month_total > 0:
        payroll_trend_percent = round(((total_monthly_payroll - last_month_total) / last_month_total) * 100, 1)
    
    # Format for frontend
    payroll_chart_data = {
        "labels": [item["month"] for item in payroll_trend],
        "datasets": [{
            "label": "Net Payroll",
            "data": [item["amount"] for item in payroll_trend],
            "borderColor": "rgb(59, 130, 246)",
            "backgroundColor": "rgba(59, 130, 246, 0.1)",
            "tension": 0.4
        }]
    }
    
    department_chart_data = {
        "labels": [item["name"] for item in dept_distribution],
        "datasets": [{
            "data": [item["count"] for item in dept_distribution],
            "backgroundColor": [
                'rgba(59, 130, 246, 0.8)',
                'rgba(5, 150, 105, 0.8)',
                'rgba(217, 119, 6, 0.8)',
                'rgba(100, 116, 139, 0.8)',
                'rgba(220, 38, 38, 0.8)'
            ]
        }]
    }
    
    # Recent activity
    recent_activity = []
    for emp in db.query(User).filter(User.deletion_status == 0).order_by(User.created_at.desc()).limit(5).all():
        recent_activity.append({
            "id": emp.id,
            "description": f"New employee {emp.name} joined",
            "type": "success",
            "timestamp": emp.created_at.isoformat()
        })
    
    return {
        "total_employees": total_employees,
        "employees_trend": 5.2,
        "active_today": today_attendance,
        "active_trend": 2.1,
        "pending_leaves": pending_leaves,
        "leaves_trend": 0,
        "monthly_payroll": total_monthly_payroll,
        "payroll_trend": payroll_trend_percent,
        "payroll_chart": payroll_chart_data,
        "department_chart": department_chart_data,
        "recent_activity": recent_activity
    }


@router.get("/recent-activity")
async def get_recent_activity(
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """
    Get recent activity feed
    """
    activities = []
    
    # Recent employees
    recent_employees = db.query(User).filter(
        User.deletion_status == 0
    ).order_by(User.created_at.desc()).limit(5).all()
    
    for emp in recent_employees:
        activities.append({
            "type": "employee_added",
            "description": f"New employee {emp.name} joined",
            "timestamp": emp.created_at.isoformat(),
            "icon": "user-plus"
        })
    
    # Recent leave requests
    recent_leaves = db.query(Leave).order_by(
        Leave.created_at.desc()
    ).limit(5).all()
    
    for leave in recent_leaves:
        activities.append({
            "type": "leave_request",
            "description": f"Leave request from employee ID {leave.employee_id}",
            "timestamp": leave.created_at.isoformat(),
            "icon": "calendar",
            "status": leave.get_status_name()
        })
    
    # Sort by timestamp
    activities.sort(key=lambda x: x["timestamp"], reverse=True)
    
    return {
        "activities": activities[:10]
    }