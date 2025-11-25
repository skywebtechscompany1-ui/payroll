"""
Notifications API endpoints
"""

from typing import Any, List
from datetime import datetime
from fastapi import APIRouter, Depends, HTTPException
from sqlalchemy.orm import Session
from sqlalchemy import desc

from app.api import deps
from app.models.notification import Notification
from app.models.activity_log import ActivityLog

router = APIRouter()


@router.get("")
async def get_notifications(
    skip: int = 0,
    limit: int = 50,
    unread_only: bool = False,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """
    Get user notifications
    """
    query = db.query(Notification).filter(
        Notification.user_id == current_user["id"]
    )
    
    if unread_only:
        query = query.filter(Notification.is_read == False)
    
    notifications = query.order_by(desc(Notification.created_at)).offset(skip).limit(limit).all()
    
    # Count unread
    unread_count = db.query(Notification).filter(
        Notification.user_id == current_user["id"],
        Notification.is_read == False
    ).count()
    
    return {
        "notifications": [
            {
                "id": n.id,
                "title": n.title,
                "message": n.message,
                "type": n.type,
                "is_read": n.is_read,
                "action_url": n.action_url,
                "action_text": n.action_text,
                "created_at": n.created_at.isoformat(),
                "read_at": n.read_at.isoformat() if n.read_at else None
            }
            for n in notifications
        ],
        "unread_count": unread_count,
        "total": len(notifications)
    }


@router.post("/{notification_id}/toggle-read")
async def toggle_notification_read(
    notification_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """
    Toggle notification read status
    """
    notification = db.query(Notification).filter(
        Notification.id == notification_id,
        Notification.user_id == current_user["id"]
    ).first()
    
    if not notification:
        raise HTTPException(status_code=404, detail="Notification not found")
    
    notification.is_read = not notification.is_read
    notification.read_at = datetime.now() if notification.is_read else None
    db.commit()
    
    return {
        "success": True,
        "is_read": notification.is_read
    }


@router.post("/mark-all-read")
async def mark_all_notifications_read(
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """
    Mark all notifications as read
    """
    notifications = db.query(Notification).filter(
        Notification.user_id == current_user["id"],
        Notification.is_read == False
    ).all()
    
    for notification in notifications:
        notification.is_read = True
        notification.read_at = datetime.now()
    
    db.commit()
    
    return {
        "success": True,
        "marked_count": len(notifications)
    }


@router.get("/activity-logs")
async def get_activity_logs(
    skip: int = 0,
    limit: int = 100,
    action_filter: str = None,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """
    Get activity logs (admin only)
    """
    # Check if user is admin
    if current_user.get("access_label", 5) not in [1, 2]:  # 1=superadmin, 2=admin
        raise HTTPException(status_code=403, detail="Admin access required")
    
    query = db.query(ActivityLog).join(ActivityLog.user)
    
    if action_filter:
        query = query.filter(ActivityLog.action.contains(action_filter))
    
    logs = query.order_by(desc(ActivityLog.created_at)).offset(skip).limit(limit).all()
    
    return {
        "logs": [
            {
                "id": log.id,
                "user_id": log.user_id,
                "user_name": log.user.name if log.user else "Unknown",
                "user_email": log.user.email if log.user else "Unknown",
                "action": log.action,
                "module": log.module,
                "description": log.description,
                "ip_address": log.ip_address,
                "user_agent": log.user_agent,
                "extra_data": log.extra_data,
                "status": log.status,
                "error_message": log.error_message,
                "created_at": log.created_at.isoformat()
            }
            for log in logs
        ],
        "total": len(logs)
    }
