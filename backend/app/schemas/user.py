"""
User schemas
"""

from typing import Optional, List
from datetime import datetime, date
from pydantic import BaseModel, EmailStr, validator


class UserBase(BaseModel):
    name: str
    email: Optional[EmailStr] = None
    employee_id: Optional[str] = None
    father_name: Optional[str] = None
    mother_name: Optional[str] = None
    spouse_name: Optional[str] = None
    present_address: str
    permanent_address: Optional[str] = None
    home_district: Optional[str] = None
    contact_no_one: str
    contact_no_two: Optional[str] = None
    emergency_contact: Optional[str] = None
    web: Optional[str] = None
    gender: str  # M, F, O
    date_of_birth: Optional[date] = None
    marital_status: Optional[int] = None
    id_name: Optional[int] = None
    id_number: Optional[str] = None
    academic_qualification: Optional[str] = None
    professional_qualification: Optional[str] = None
    experience: Optional[str] = None
    reference: Optional[str] = None
    joining_date: Optional[date] = None
    designation_id: Optional[int] = None
    role: Optional[str] = "employee"
    avatar: Optional[str] = None
    account_name: Optional[str] = None
    bank_acc_no: Optional[str] = None
    bank_name: Optional[str] = None
    bank_branch: Optional[str] = None
    bank_sort_code: Optional[str] = None
    kin_details_name: Optional[str] = None
    kin_details_relation: Optional[str] = None
    kin_details_phone: Optional[str] = None
    kra_pin: Optional[str] = None
    nssf_no: Optional[str] = None
    nhif_no: Optional[str] = None

    @validator('gender')
    def validate_gender(cls, v):
        if v.upper() not in ['M', 'F', 'O']:
            raise ValueError('Gender must be M, F, or O')
        return v.upper()

    @validator('role')
    def validate_role(cls, v):
        allowed_roles = ['superadmin', 'admin', 'hr', 'manager', 'employee']
        if v not in allowed_roles:
            raise ValueError(f'Role must be one of: {", ".join(allowed_roles)}')
        return v


class UserCreate(UserBase):
    password: str

    @validator('password')
    def validate_password(cls, v):
        if len(v) < 8:
            raise ValueError('Password must be at least 8 characters long')
        if not any(c.isupper() for c in v):
            raise ValueError('Password must contain at least one uppercase letter')
        if not any(c.islower() for c in v):
            raise ValueError('Password must contain at least one lowercase letter')
        if not any(c.isdigit() for c in v):
            raise ValueError('Password must contain at least one digit')
        return v


class UserUpdate(BaseModel):
    name: Optional[str] = None
    email: Optional[EmailStr] = None
    employee_id: Optional[str] = None
    father_name: Optional[str] = None
    mother_name: Optional[str] = None
    spouse_name: Optional[str] = None
    present_address: Optional[str] = None
    permanent_address: Optional[str] = None
    home_district: Optional[str] = None
    contact_no_one: Optional[str] = None
    contact_no_two: Optional[str] = None
    emergency_contact: Optional[str] = None
    web: Optional[str] = None
    gender: Optional[str] = None
    date_of_birth: Optional[date] = None
    marital_status: Optional[int] = None
    id_name: Optional[int] = None
    id_number: Optional[str] = None
    academic_qualification: Optional[str] = None
    professional_qualification: Optional[str] = None
    experience: Optional[str] = None
    reference: Optional[str] = None
    joining_date: Optional[date] = None
    designation_id: Optional[int] = None
    role: Optional[str] = None
    avatar: Optional[str] = None
    account_name: Optional[str] = None
    bank_acc_no: Optional[str] = None
    bank_name: Optional[str] = None
    bank_branch: Optional[str] = None
    bank_sort_code: Optional[str] = None
    kin_details_name: Optional[str] = None
    kin_details_relation: Optional[str] = None
    kin_details_phone: Optional[str] = None
    kra_pin: Optional[str] = None
    nssf_no: Optional[str] = None
    nhif_no: Optional[str] = None


class UserResponse(BaseModel):
    id: int
    name: str
    email: Optional[str] = None
    employee_id: Optional[str] = None
    name_prefix: Optional[str] = None
    display_name: str
    present_address: str
    permanent_address: Optional[str] = None
    contact_no_one: str
    contact_no_two: Optional[str] = None
    emergency_contact: Optional[str] = None
    web: Optional[str] = None
    gender: str
    date_of_birth: Optional[date] = None
    marital_status: Optional[int] = None
    id_number: Optional[str] = None
    academic_qualification: Optional[str] = None
    professional_qualification: Optional[str] = None
    experience: Optional[str] = None
    reference: Optional[str] = None
    joining_date: Optional[date] = None
    designation_id: Optional[int] = None
    role: Optional[str] = None
    access_label: int
    activation_status: int
    avatar: Optional[str] = None
    account_name: Optional[str] = None
    bank_acc_no: Optional[str] = None
    bank_name: Optional[str] = None
    bank_branch: Optional[str] = None
    bank_sort_code: Optional[str] = None
    kin_details_name: Optional[str] = None
    kin_details_relation: Optional[str] = None
    kin_details_phone: Optional[str] = None
    kra_pin: Optional[str] = None
    nssf_no: Optional[str] = None
    nhif_no: Optional[str] = None
    created_at: datetime
    updated_at: datetime

    @property
    def is_active(self) -> bool:
        return self.activation_status == 1

    class Config:
        from_attributes = True


class UserListResponse(BaseModel):
    users: List[UserResponse]
    total: int
    page: int
    page_size: int
    pages: int