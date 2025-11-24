"""
Security utilities for authentication and authorization
"""

from datetime import datetime, timedelta
from typing import Optional, Union, Any
from jose import jwt
from passlib.context import CryptContext
from fastapi import HTTPException, status

from app.core.config import settings

# Password hashing context
pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")


def create_access_token(
    subject: Union[str, Any],
    expires_delta: Optional[timedelta] = None
) -> str:
    """
    Create JWT access token
    """
    if expires_delta:
        expire = datetime.utcnow() + expires_delta
    else:
        expire = datetime.utcnow() + timedelta(
            minutes=settings.ACCESS_TOKEN_EXPIRE_MINUTES
        )

    to_encode = {"exp": expire, "sub": str(subject), "type": "access"}
    encoded_jwt = jwt.encode(
        to_encode,
        settings.SECRET_KEY,
        algorithm=settings.ALGORITHM
    )
    return encoded_jwt


def create_refresh_token(
    subject: Union[str, Any],
    expires_delta: Optional[timedelta] = None
) -> str:
    """
    Create JWT refresh token
    """
    if expires_delta:
        expire = datetime.utcnow() + expires_delta
    else:
        expire = datetime.utcnow() + timedelta(
            days=settings.REFRESH_TOKEN_EXPIRE_DAYS
        )

    to_encode = {"exp": expire, "sub": str(subject), "type": "refresh"}
    encoded_jwt = jwt.encode(
        to_encode,
        settings.SECRET_KEY,
        algorithm=settings.ALGORITHM
    )
    return encoded_jwt


def verify_token(token: str, token_type: str = "access") -> Optional[str]:
    """
    Verify JWT token and return subject
    """
    try:
        payload = jwt.decode(
            token,
            settings.SECRET_KEY,
            algorithms=[settings.ALGORITHM]
        )
        token_type_from_payload = payload.get("type")
        if token_type_from_payload != token_type:
            return None

        subject: str = payload.get("sub")
        if subject is None:
            return None

        return subject
    except jwt.JWTError:
        return None


def verify_password(plain_password: str, hashed_password: str) -> bool:
    """
    Verify password against hash
    """
    return pwd_context.verify(plain_password, hashed_password)


def get_password_hash(password: str) -> str:
    """
    Hash password
    """
    return pwd_context.hash(password)


class RolePermissions:
    """
    Role-based access control permissions
    """

    ROLES = {
        "superadmin": 1,
        "admin": 2,
        "hr": 3,
        "manager": 4,
        "employee": 5
    }

    PERMISSIONS = {
        # User management
        "users:create": ["superadmin", "admin", "hr"],
        "users:read": ["superadmin", "admin", "hr", "manager"],
        "users:update": ["superadmin", "admin", "hr"],
        "users:delete": ["superadmin", "admin"],

        # Employee management
        "employees:create": ["superadmin", "admin", "hr"],
        "employees:read": ["superadmin", "admin", "hr", "manager", "employee"],
        "employees:update": ["superadmin", "admin", "hr", "manager"],
        "employees:delete": ["superadmin", "admin"],

        # Payroll management
        "payroll:create": ["superadmin", "admin", "hr"],
        "payroll:read": ["superadmin", "admin", "hr", "manager"],
        "payroll:update": ["superadmin", "admin", "hr"],
        "payroll:delete": ["superadmin", "admin"],
        "payroll:process": ["superadmin", "admin", "hr"],

        # Attendance management
        "attendance:create": ["superadmin", "admin", "hr", "manager", "employee"],
        "attendance:read": ["superadmin", "admin", "hr", "manager", "employee"],
        "attendance:update": ["superadmin", "admin", "hr", "manager"],
        "attendance:delete": ["superadmin", "admin", "hr"],

        # Leave management
        "leave:create": ["superadmin", "admin", "hr", "manager", "employee"],
        "leave:read": ["superadmin", "admin", "hr", "manager", "employee"],
        "leave:update": ["superadmin", "admin", "hr", "manager"],
        "leave:delete": ["superadmin", "admin", "hr"],
        "leave:approve": ["superadmin", "admin", "hr", "manager"],

        # Reports
        "reports:create": ["superadmin", "admin", "hr", "manager"],
        "reports:read": ["superadmin", "admin", "hr", "manager", "employee"],
        "reports:update": ["superadmin", "admin", "hr", "manager"],
        "reports:delete": ["superadmin", "admin"],

        # System settings
        "settings:create": ["superadmin", "admin"],
        "settings:read": ["superadmin", "admin", "hr"],
        "settings:update": ["superadmin", "admin"],
        "settings:delete": ["superadmin", "admin"],
    }

    @classmethod
    def has_permission(cls, user_role: str, permission: str) -> bool:
        """
        Check if user role has permission
        """
        allowed_roles = cls.PERMISSIONS.get(permission, [])
        return user_role in allowed_roles

    @classmethod
    def check_permission(cls, user_role: str, permission: str):
        """
        Raise exception if user doesn't have permission
        """
        if not cls.has_permission(user_role, permission):
            raise HTTPException(
                status_code=status.HTTP_403_FORBIDDEN,
                detail="Insufficient permissions"
            )