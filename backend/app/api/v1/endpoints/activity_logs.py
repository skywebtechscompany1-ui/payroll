"""
Activity Log endpoints
"""

from typing import Any, Optional
from datetime import datetime
from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy.orm import Session
from sqlalchemy import desc

from app.api import deps
from app.models.activity_log import ActivityLog
from app.schemas.activity_log import ActivityLogCreate, ActivityLogResponse, ActivityLogList

router = APIRouter()


@router.get("/", response_model=ActivityLogList)
async def get_activity_logs(
    skip: int = Query(0, ge=0),
    limit: int = Query(100, ge=1, le=100),
    user_id: Optional[int] = Query(None),
    action: Optional[str] = Query(None),
    module: Optional[str] = Query(None),
    status: Optional[str] = Query(None),
    start_date: Optional[datetime] = Query(None),
    end_date: Optional[datetime] = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("activity_logs:read"))
) -> Any:
    """Get activity logs with filters"""
    query = db.query(ActivityLog)
    
    if user_id:
        query = query.filter(ActivityLog.user_id == user_id)
    
    if action:
        query = query.filter(ActivityLog.action.ilike(f"%{action}%"))
    
    if module:
        query = query.filter(ActivityLog.module == module)
    
    if status:
        query = query.filter(ActivityLog.status == status)
    
    if start_date:
        query = query.filter(ActivityLog.created_at >= start_date)
    
    if end_date:
        query = query.filter(ActivityLog.created_at <= end_date)
    
    total = query.count()
    logs = query.order_by(desc(ActivityLog.created_at)).offset(skip).limit(limit).all()
    
    return {"total": total, "items": logs}


@router.get("/{log_id}", response_model=ActivityLogResponse)
async def get_activity_log(
    log_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("activity_logs:read"))
) -> Any:
    """Get activity log by ID"""
    log = db.query(ActivityLog).filter(ActivityLog.id == log_id).first()
    
    if not log:
        raise HTTPException(status_code=404, detail="Activity log not found")
    
    return log


@router.post("/", response_model=ActivityLogResponse)
async def create_activity_log(
    log_in: ActivityLogCreate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """Create activity log (internal use)"""
    log = ActivityLog(**log_in.dict())
    db.add(log)
    db.commit()
    db.refresh(log)
    
    return log


@router.get("/user/{user_id}/recent", response_model=ActivityLogList)
async def get_user_recent_activity(
    user_id: int,
    limit: int = Query(20, ge=1, le=100),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """Get recent activity for a specific user"""
    # Users can only view their own logs unless they have permission
    if current_user["id"] != user_id:
        # Check if user has permission to view others' logs
        deps.get_current_user_with_permission("activity_logs:read")(current_user)
    
    logs = db.query(ActivityLog).filter(
        ActivityLog.user_id == user_id
    ).order_by(desc(ActivityLog.created_at)).limit(limit).all()
    
    return {"total": len(logs), "items": logs}


@router.get("/stats/summary")
async def get_activity_stats(
    start_date: Optional[datetime] = Query(None),
    end_date: Optional[datetime] = Query(None),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("activity_logs:read"))
) -> Any:
    """Get activity statistics"""
    query = db.query(ActivityLog)
    
    if start_date:
        query = query.filter(ActivityLog.created_at >= start_date)
    
    if end_date:
        query = query.filter(ActivityLog.created_at <= end_date)
    
    logs = query.all()
    
    # Count by module
    by_module = {}
    by_action = {}
    by_status = {}
    
    for log in logs:
        by_module[log.module] = by_module.get(log.module, 0) + 1
        by_action[log.action] = by_action.get(log.action, 0) + 1
        by_status[log.status] = by_status.get(log.status, 0) + 1
    
    return {
        "total_activities": len(logs),
        "by_module": by_module,
        "by_action": by_action,
        "by_status": by_status
    }
