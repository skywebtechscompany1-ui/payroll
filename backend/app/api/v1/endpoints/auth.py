"""
Authentication endpoints
"""

from datetime import timedelta
from typing import Any
from fastapi import APIRouter, Depends, HTTPException, status, Request
from fastapi.security import OAuth2PasswordRequestForm
from sqlalchemy.orm import Session
import logging

from app.api import deps
from app.core.config import settings
from app.core.security import (
    create_access_token,
    create_refresh_token,
    verify_token,
    verify_password,
    RolePermissions
)
from app.core.redis_client import (
    blacklist_token,
    is_token_blacklisted,
    store_session,
    revoke_session,
    revoke_all_sessions,
    check_rate_limit,
    track_failed_login,
    reset_failed_logins,
    is_account_locked,
    get_lock_remaining_time
)
from app.schemas.auth import Token, TokenPayload, UserLogin, UserRegister, RefreshTokenRequest
from app.services.user_service import UserService

logger = logging.getLogger(__name__)

router = APIRouter()


@router.post("/login", response_model=Token)
async def login(
    request: Request,
    db: Session = Depends(deps.get_db),
    form_data: OAuth2PasswordRequestForm = Depends()
) -> Any:
    """
    OAuth2 compatible token login with rate limiting and account lockout
    """
    user_service = UserService(db)
    client_ip = request.client.host if request.client else "unknown"
    
    # Rate limiting: 5 login attempts per minute per IP
    if not check_rate_limit(f"login:{client_ip}", limit=5, window=60):
        logger.warning(f"Rate limit exceeded for IP: {client_ip}")
        raise HTTPException(
            status_code=status.HTTP_429_TOO_MANY_REQUESTS,
            detail="Too many login attempts. Please try again later."
        )
    
    # Check if account is locked
    if is_account_locked(form_data.username):
        remaining_time = get_lock_remaining_time(form_data.username)
        logger.warning(f"Login attempt on locked account: {form_data.username}")
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN,
            detail=f"Account locked due to multiple failed attempts. Try again in {remaining_time // 60} minutes."
        )

    # Authenticate user
    user = user_service.authenticate(
        email=form_data.username,
        password=form_data.password
    )

    if not user:
        # Track failed login attempt
        attempts = track_failed_login(form_data.username)
        logger.warning(f"Failed login attempt for {form_data.username} from {client_ip}. Attempts: {attempts}")
        
        if attempts >= 5:
            logger.error(f"Account locked: {form_data.username}")
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Account locked due to multiple failed attempts. Try again in 15 minutes."
            )
        
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Incorrect email or password",
            headers={"WWW-Authenticate": "Bearer"},
        )

    if not user.is_active:
        logger.warning(f"Login attempt on inactive account: {user.email}")
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Inactive user"
        )

    # Reset failed login attempts on successful login
    reset_failed_logins(form_data.username)

    # Create tokens
    access_token_expires = timedelta(minutes=settings.ACCESS_TOKEN_EXPIRE_MINUTES)
    access_token = create_access_token(
        subject=user.id,
        expires_delta=access_token_expires
    )

    refresh_token = create_refresh_token(subject=user.id)

    # Store session in Redis
    store_session(
        user_id=user.id,
        token=access_token,
        expires_in=settings.ACCESS_TOKEN_EXPIRE_MINUTES * 60,
        metadata={
            "ip": client_ip,
            "user_agent": request.headers.get("user-agent", "unknown"),
            "email": user.email
        }
    )
    
    logger.info(f"Successful login: {user.email} from {client_ip}")

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
    request_body: RefreshTokenRequest,
    request: Request,
    db: Session = Depends(deps.get_db)
) -> Any:
    """
    Refresh access token using refresh token
    """
    # Verify refresh token
    user_id = verify_token(request_body.refresh_token, "refresh")

    if not user_id:
        logger.warning("Invalid refresh token attempt")
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Invalid refresh token"
        )

    user_service = UserService(db)
    user = user_service.get(int(user_id))

    if not user or not user.is_active:
        logger.warning(f"Token refresh for invalid/inactive user: {user_id}")
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

    new_refresh_token = create_refresh_token(subject=user.id)
    
    # Store new session
    client_ip = request.client.host if request.client else "unknown"
    store_session(
        user_id=user.id,
        token=access_token,
        expires_in=settings.ACCESS_TOKEN_EXPIRE_MINUTES * 60,
        metadata={
            "ip": client_ip,
            "user_agent": request.headers.get("user-agent", "unknown"),
            "email": user.email
        }
    )
    
    logger.info(f"Token refreshed for user: {user.email}")

    return {
        "access_token": access_token,
        "refresh_token": new_refresh_token,
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
    Logout user - blacklist token and revoke session
    """
    # Get token from Authorization header
    auth_header = request.headers.get("Authorization", "")
    if auth_header.startswith("Bearer "):
        token = auth_header[7:]
        
        # Blacklist the access token
        blacklist_token(token, settings.ACCESS_TOKEN_EXPIRE_MINUTES * 60)
        
        # Revoke session
        revoke_session(current_user["id"], token)
        
        logger.info(f"User logged out: {current_user.get('email', 'unknown')}")
    
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
    Send password reset email - generates reset token
    """
    import secrets
    from app.core.redis_client import RedisClient
    
    user_service = UserService(db)
    user = user_service.get_by_email(email)

    if not user:
        # Don't reveal if user exists (security best practice)
        logger.info(f"Password reset requested for non-existent email: {email}")
        return {"message": "If email exists, password reset instructions have been sent"}

    # Generate secure reset token
    reset_token = secrets.token_urlsafe(32)
    
    # Store token in Redis with 1-hour TTL
    try:
        redis_client = RedisClient.get_client()
        redis_client.setex(
            f"password_reset:{reset_token}",
            3600,  # 1 hour
            str(user.id)
        )
        logger.info(f"Password reset token generated for user: {user.email}")
    except Exception as e:
        logger.error(f"Failed to store reset token: {e}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Failed to generate reset token"
        )
    
    # TODO: Send email with reset link
    # reset_link = f"http://localhost:3000/reset-password?token={reset_token}"
    # send_email(user.email, "Password Reset", reset_link)
    
    logger.info(f"Password reset token: {reset_token} (for development only)")
    
    return {
        "message": "If email exists, password reset instructions have been sent",
        "token": reset_token if settings.DEBUG else None  # Only in debug mode
    }


@router.post("/reset-password")
async def reset_password(
    reset_data: dict,
    db: Session = Depends(deps.get_db)
) -> Any:
    """
    Reset password using token from forgot-password
    """
    from app.core.redis_client import RedisClient
    from app.core.security import get_password_hash
    
    token = reset_data.get("token")
    new_password = reset_data.get("new_password")
    
    if not token or not new_password:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Token and new password are required"
        )
    
    # Validate password strength
    if len(new_password) < 8:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="Password must be at least 8 characters long"
        )
    
    # Verify reset token
    try:
        redis_client = RedisClient.get_client()
        user_id = redis_client.get(f"password_reset:{token}")
        
        if not user_id:
            logger.warning(f"Invalid or expired reset token attempted")
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Invalid or expired reset token"
            )
        
        # Get user
        user_service = UserService(db)
        user = user_service.get(int(user_id))
        
        if not user:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="User not found"
            )
        
        # Update password
        user.hashed_password = get_password_hash(new_password)
        db.commit()
        
        # Delete reset token (one-time use)
        redis_client.delete(f"password_reset:{token}")
        
        # Revoke all user sessions for security
        from app.core.redis_client import revoke_all_sessions
        revoke_all_sessions(user.id)
        
        logger.info(f"Password reset successful for user: {user.email}")
        
        return {"message": "Password has been reset successfully. Please login with your new password."}
        
    except HTTPException:
        raise
    except Exception as e:
        logger.error(f"Password reset failed: {e}")
        raise HTTPException(
            status_code=status.HTTP_500_INTERNAL_SERVER_ERROR,
            detail="Failed to reset password"
        )