"""
Role model - SQLAlchemy ORM model for user roles and permissions
"""

from sqlalchemy import Column, Integer, String, Boolean, DateTime, Text, JSON
from sqlalchemy.sql import func

from app.core.database import Base


class Role(Base):
    __tablename__ = "roles"

    id = Column(Integer, primary_key=True, index=True)
    name = Column(String(50), unique=True, nullable=False, index=True)
    display_name = Column(String(100), nullable=False)
    description = Column(Text, nullable=True)
    
    # Permissions stored as JSON
    permissions = Column(JSON, nullable=False, default={})
    
    # System fields
    is_system = Column(Boolean, default=False)  # System roles cannot be deleted
    is_active = Column(Boolean, default=True)
    
    # Timestamps
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    def has_permission(self, permission: str) -> bool:
        """Check if role has a specific permission"""
        if not self.permissions:
            return False
        
        # Permission format: "module:action" e.g., "users:read"
        parts = permission.split(":")
        if len(parts) != 2:
            return False
        
        module, action = parts
        return self.permissions.get(module, {}).get(action, False)

    def add_permission(self, module: str, action: str):
        """Add a permission to the role"""
        if not self.permissions:
            self.permissions = {}
        
        if module not in self.permissions:
            self.permissions[module] = {}
        
        self.permissions[module][action] = True

    def remove_permission(self, module: str, action: str):
        """Remove a permission from the role"""
        if not self.permissions or module not in self.permissions:
            return
        
        if action in self.permissions[module]:
            del self.permissions[module][action]
