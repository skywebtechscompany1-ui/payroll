"""
User management endpoints
"""

from typing import Any, Optional
from fastapi import APIRouter, Depends, HTTPException, status, Query
from sqlalchemy.orm import Session

from app.api import deps
from app.core.security import RolePermissions
from app.schemas.user import UserCreate, UserUpdate, UserResponse, UserListResponse
from app.services.user_service import UserService

router = APIRouter()


@router.get("/", response_model=UserListResponse)
async def get_users(
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("users:read")),
    page: int = Query(1, ge=1),
    page_size: int = Query(20, ge=1, le=100),
    search: Optional[str] = Query(None),
    role: Optional[str] = Query(None),
    activation_status: Optional[int] = Query(None)
) -> Any:
    """
    Get list of users with pagination and filtering
    """
    user_service = UserService(db)

    skip = (page - 1) * page_size

    users = user_service.get_list(
        skip=skip,
        limit=page_size,
        search=search,
        role=role,
        activation_status=activation_status
    )

    total = user_service.count(
        search=search,
        role=role,
        activation_status=activation_status
    )

    return UserListResponse(
        users=[UserResponse.from_orm(user) for user in users],
        total=total,
        page=page,
        page_size=page_size,
        pages=(total + page_size - 1) // page_size
    )


@router.get("/{user_id}", response_model=UserResponse)
async def get_user(
    user_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("users:read"))
) -> Any:
    """
    Get user by ID
    """
    user_service = UserService(db)
    user = user_service.get(user_id)

    if not user:
        raise HTTPException(
            status_code=status.HTTP_404_NOT_FOUND,
            detail="User not found"
        )

    return UserResponse.from_orm(user)


@router.post("/", response_model=UserResponse)
async def create_user(
    user_data: UserCreate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("users:create"))
) -> Any:
    """
    Create new user
    """
    user_service = UserService(db)

    # Set created_by
    user_dict = user_data.dict()
    user_dict["created_by"] = current_user["id"]

    user = user_service.create(UserCreate(**user_dict))
    return UserResponse.from_orm(user)


@router.put("/{user_id}", response_model=UserResponse)
async def update_user(
    user_id: int,
    user_data: UserUpdate,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("users:update"))
) -> Any:
    """
    Update user information
    """
    user_service = UserService(db)
    user = user_service.update(user_id, user_data.dict(exclude_unset=True))

    return UserResponse.from_orm(user)


@router.delete("/{user_id}")
async def delete_user(
    user_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("users:delete"))
) -> Any:
    """
    Soft delete user
    """
    user_service = UserService(db)
    user_service.delete(user_id)

    return {"message": "User deleted successfully"}


@router.post("/{user_id}/activate")
async def activate_user(
    user_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("users:update"))
) -> Any:
    """
    Activate user account
    """
    user_service = UserService(db)
    user = user_service.activate(user_id)

    return UserResponse.from_orm(user)


@router.post("/{user_id}/deactivate")
async def deactivate_user(
    user_id: int,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("users:update"))
) -> Any:
    """
    Deactivate user account
    """
    user_service = UserService(db)
    user = user_service.deactivate(user_id)

    return UserResponse.from_orm(user)