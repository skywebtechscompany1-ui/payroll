"""
Authentication endpoints
"""

from datetime import timedelta
from typing import Any
from fastapi import APIRouter, Depends, HTTPException, status, Request
from fastapi.security import OAuth2PasswordRequestForm
from sqlalchemy.orm import Session

from app.api import deps
from app.core.config import settings
from app.core.security import (
    create_access_token,
    create_refresh_token,
    verify_token,
    verify_password,
    RolePermissions
)
from app.schemas.auth import Token, TokenPayload, UserLogin, UserRegister, RefreshTokenRequest
from app.services.user_service import UserService

router = APIRouter()


@router.post("/login", response_model=Token)
async def login(
    request: Request,
    db: Session = Depends(deps.get_db),
    form_data: OAuth2PasswordRequestForm = Depends()
) -> Any:
    """
    OAuth2 compatible token login, get an access token for future requests
    """
    user_service = UserService(db)

    # Authenticate user
    user = user_service.authenticate(
        email=form_data.username,
        password=form_data.password
    )

    if not user:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect email or password",
            headers={"WWW-Authenticate": "Bearer"},
        )

    if not user.is_active:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Inactive user"
        )

    # Create tokens
    access_token_expires = timedelta(minutes=settings.ACCESS_TOKEN_EXPIRE_MINUTES)
    access_token = create_access_token(
        subject=user.id,
        expires_delta=access_token_expires
    )

    refresh_token = create_refresh_token(subject=user.id)

    # Store refresh token in Redis/database (implementation needed)

    return {
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "bearer",
        "expires_in": settings.ACCESS_TOKEN_EXPIRE_MINUTES * 60,
        "user": {
            "id": user.id,
            "email": user.email,
            "name": user.name,
            "role": user.role,
            "employee_id": user.employee_id
        }
    }


@router.post("/refresh", response_model=Token)
async def refresh_token(
    request: RefreshTokenRequest,
    db: Session = Depends(deps.get_db)
) -> Any:
    """
    Refresh access token using refresh token
    """
    # Verify refresh token
    user_id = verify_token(request.refresh_token, "refresh")

    if not user_id:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid refresh token"
        )

    user_service = UserService(db)
    user = user_service.get(int(user_id))

    if not user or not user.is_active:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid user"
        )

    # Create new tokens
    access_token_expires = timedelta(minutes=settings.ACCESS_TOKEN_EXPIRE_MINUTES)
    access_token = create_access_token(
        subject=user.id,
        expires_delta=access_token_expires
    )

    refresh_token = create_refresh_token(subject=user.id)

    return {
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "bearer",
        "expires_in": settings.ACCESS_TOKEN_EXPIRE_MINUTES * 60,
        "user": {
            "id": user.id,
            "email": user.email,
            "name": user.name,
            "role": user.role,
            "employee_id": user.employee_id
        }
    }


@router.post("/register", response_model=Token)
async def register(
    user_data: UserRegister,
    db: Session = Depends(deps.get_db)
) -> Any:
    """
    Register new user
    """
    user_service = UserService(db)

    # Check if user already exists
    if user_service.get_by_email(user_data.email):
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="User with this email already exists"
        )

    # Create user
    user = user_service.create(user_data)

    # Create tokens
    access_token_expires = timedelta(minutes=settings.ACCESS_TOKEN_EXPIRE_MINUTES)
    access_token = create_access_token(
        subject=user.id,
        expires_delta=access_token_expires
    )

    refresh_token = create_refresh_token(subject=user.id)

    return {
        "access_token": access_token,
        "refresh_token": refresh_token,
        "token_type": "bearer",
        "expires_in": settings.ACCESS_TOKEN_EXPIRE_MINUTES * 60,
        "user": {
            "id": user.id,
            "email": user.email,
            "name": user.name,
            "role": user.role,
            "employee_id": user.employee_id
        }
    }


@router.post("/logout")
async def logout(
    request: Request,
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """
    Logout user (invalidate tokens)
    """
    # In a real implementation, you would invalidate the refresh token
    # stored in Redis or database
    return {"message": "Successfully logged out"}


@router.get("/me")
async def get_current_user_info(
    current_user: dict = Depends(deps.get_current_user)
) -> Any:
    """
    Get current user information
    """
    return current_user


@router.post("/forgot-password")
async def forgot_password(
    email: str,
    db: Session = Depends(deps.get_db)
) -> Any:
    """
    Send password reset email
    """
    user_service = UserService(db)
    user = user_service.get_by_email(email)

    if not user:
        # Don't reveal if user exists
        return {"message": "If email exists, password reset instructions have been sent"}

    # Generate password reset token and send email
    # Implementation needed for email sending
    return {"message": "If email exists, password reset instructions have been sent"}


@router.post("/reset-password")
async def reset_password(
    token: str,
    new_password: str,
    db: Session = Depends(deps.get_db)
) -> Any:
    """
    Reset password using token
    """
    # Verify password reset token and update password
    # Implementation needed for password reset token verification
    return {"message": "Password has been reset successfully"}