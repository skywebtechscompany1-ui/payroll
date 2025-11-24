"""
User service - handles user-related business logic
"""

from typing import Optional, Dict, Any, List
from sqlalchemy.orm import Session
from sqlalchemy import or_
from fastapi import HTTPException, status

from app.models.user import User
from app.schemas.auth import UserRegister
from app.core.security import get_password_hash, verify_password


class UserService:
    def __init__(self, db: Session):
        self.db = db

    def get(self, user_id: int) -> Optional[User]:
        """
        Get user by ID
        """
        return self.db.query(User).filter(
            User.id == user_id,
            User.deletion_status == 0
        ).first()

    def get_by_email(self, email: str) -> Optional[User]:
        """
        Get user by email
        """
        return self.db.query(User).filter(
            User.email == email,
            User.deletion_status == 0
        ).first()

    def get_by_employee_id(self, employee_id: str) -> Optional[User]:
        """
        Get user by employee ID
        """
        return self.db.query(User).filter(
            User.employee_id == employee_id,
            User.deletion_status == 0
        ).first()

    def authenticate(self, email: str, password: str) -> Optional[User]:
        """
        Authenticate user with email and password
        """
        user = self.get_by_email(email)
        if not user:
            return None

        if not verify_password(password, user.password):
            return None

        return user

    def create(self, user_data: UserRegister) -> User:
        """
        Create new user
        """
        # Check if email already exists
        if self.get_by_email(user_data.email):
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Email already registered"
            )

        # Generate employee ID if not provided
        if not user_data.employee_id:
            last_user = self.db.query(User).filter(
                User.employee_id.isnot(None)
            ).order_by(User.id.desc()).first()

            if last_user and last_user.employee_id:
                try:
                    last_num = int(last_user.employee_id.replace("EMP", ""))
                    new_employee_id = f"EMP{last_num + 1:04d}"
                except ValueError:
                    new_employee_id = "EMP0001"
            else:
                new_employee_id = "EMP0001"
        else:
            new_employee_id = user_data.employee_id

        # Create user object
        user = User(
            name=user_data.name,
            email=user_data.email,
            password=get_password_hash(user_data.password),
            employee_id=new_employee_id,
            present_address="",  # Required field, will be updated later
            contact_no_one="",   # Required field, will be updated later
            gender="M",          # Default, will be updated later
        )

        # Set role
        user.set_role(user_data.role)
        user.activation_status = 1  # Active by default

        # Generate name prefix
        if user.name:
            user.name_prefix = self._generate_name_prefix(user.name)

        # Save to database
        self.db.add(user)
        self.db.commit()
        self.db.refresh(user)

        return user

    def update(self, user_id: int, user_data: Dict[str, Any]) -> User:
        """
        Update user information
        """
        user = self.get(user_id)
        if not user:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="User not found"
            )

        # Update allowed fields
        allowed_fields = [
            'name', 'email', 'father_name', 'mother_name', 'spouse_name',
            'present_address', 'permanent_address', 'home_district',
            'contact_no_one', 'contact_no_two', 'emergency_contact', 'web',
            'gender', 'date_of_birth', 'marital_status', 'id_name', 'id_number',
            'academic_qualification', 'professional_qualification', 'experience',
            'reference', 'joining_date', 'designation_id', 'avatar',
            'account_name', 'bank_acc_no', 'bank_name', 'bank_branch',
            'bank_sort_code', 'kin_details_name', 'kin_details_relation',
            'kin_details_phone', 'kra_pin', 'nssf_no', 'nhif_no'
        ]

        for field, value in user_data.items():
            if field in allowed_fields and hasattr(user, field):
                setattr(user, field, value)

        # Update name prefix if name changed
        if 'name' in user_data and user_data['name']:
            user.name_prefix = self._generate_name_prefix(user.name)

        self.db.commit()
        self.db.refresh(user)

        return user

    def update_password(self, user_id: int, current_password: str, new_password: str) -> User:
        """
        Update user password
        """
        user = self.get(user_id)
        if not user:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="User not found"
            )

        # Verify current password
        if not verify_password(current_password, user.password):
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Current password is incorrect"
            )

        # Update password
        user.password = get_password_hash(new_password)
        self.db.commit()

        return user

    def activate(self, user_id: int) -> User:
        """
        Activate user account
        """
        user = self.get(user_id)
        if not user:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="User not found"
            )

        user.activation_status = 1
        self.db.commit()

        return user

    def deactivate(self, user_id: int) -> User:
        """
        Deactivate user account
        """
        user = self.get(user_id)
        if not user:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="User not found"
            )

        user.activation_status = 0
        self.db.commit()

        return user

    def delete(self, user_id: int) -> bool:
        """
        Soft delete user
        """
        user = self.get(user_id)
        if not user:
            raise HTTPException(
                status_code=status.HTTP_404_NOT_FOUND,
                detail="User not found"
            )

        user.deletion_status = 1
        self.db.commit()

        return True

    def get_list(
        self,
        skip: int = 0,
        limit: int = 20,
        search: Optional[str] = None,
        role: Optional[str] = None,
        department_id: Optional[int] = None,
        activation_status: Optional[int] = None
    ) -> List[User]:
        """
        Get list of users with filtering and pagination
        """
        query = self.db.query(User).filter(User.deletion_status == 0)

        if search:
            search_term = f"%{search}%"
            query = query.filter(
                or_(
                    User.name.ilike(search_term),
                    User.email.ilike(search_term),
                    User.employee_id.ilike(search_term)
                )
            )

        if role:
            query = query.filter(User.role == role)

        if activation_status is not None:
            query = query.filter(User.activation_status == activation_status)

        return query.offset(skip).limit(limit).all()

    def count(
        self,
        search: Optional[str] = None,
        role: Optional[str] = None,
        department_id: Optional[int] = None,
        activation_status: Optional[int] = None
    ) -> int:
        """
        Count users with filtering
        """
        query = self.db.query(User).filter(User.deletion_status == 0)

        if search:
            search_term = f"%{search}%"
            query = query.filter(
                or_(
                    User.name.ilike(search_term),
                    User.email.ilike(search_term),
                    User.employee_id.ilike(search_term)
                )
            )

        if role:
            query = query.filter(User.role == role)

        if activation_status is not None:
            query = query.filter(User.activation_status == activation_status)

        return query.count()

    def _generate_name_prefix(self, name: str) -> str:
        """
        Generate 3-letter prefix from name
        """
        segments = name.strip().split()
        initials = ""

        for segment in segments[:3]:  # Take first 3 segments max
            if segment:
                initials += segment[0].upper()

        return initials[:3]