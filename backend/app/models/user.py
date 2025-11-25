"""
User model - SQLAlchemy ORM model
"""

from sqlalchemy import Column, Integer, String, Boolean, DateTime, Text, Date, SmallInteger, ForeignKey
from sqlalchemy.sql import func
from sqlalchemy.orm import relationship

from app.core.database import Base


class User(Base):
    __tablename__ = "users"

    # Basic information
    id = Column(Integer, primary_key=True, index=True)
    created_by = Column(Integer, nullable=True)
    employee_id = Column(String(20), unique=True, index=True, nullable=True)
    name = Column(String(255), nullable=False)
    name_prefix = Column(String(10), nullable=True)
    email = Column(String(100), unique=True, index=True, nullable=True)
    password = Column(String(255), nullable=False)

    # Family information
    father_name = Column(String(255), nullable=True)
    mother_name = Column(String(255), nullable=True)
    spouse_name = Column(String(255), nullable=True)

    # Contact information
    present_address = Column(String(255), nullable=False)
    permanent_address = Column(String(255), nullable=True)
    home_district = Column(String(100), nullable=True)
    contact_no_one = Column(String(30), nullable=False)
    contact_no_two = Column(String(30), nullable=True)
    emergency_contact = Column(String(30), nullable=True)
    web = Column(String(255), nullable=True)

    # Personal information
    gender = Column(String(1), nullable=False)  # M, F, O
    date_of_birth = Column(Date, nullable=True)
    marital_status = Column(SmallInteger, nullable=True)  # 1: Married, 2: Single, 3: Divorced, 4: Separated, 5: Widowed

    # Identification
    id_name = Column(SmallInteger, nullable=True)  # 1: NID, 2: Passport, 3: Driving License
    id_number = Column(String(255), nullable=True)
    kra_pin = Column(Text, nullable=True)
    nssf_no = Column(String(50), nullable=True)
    nhif_no = Column(String(50), nullable=True)
    passport_picture = Column(String(255), nullable=True)

    # Professional information
    academic_qualification = Column(Text, nullable=True)
    professional_qualification = Column(Text, nullable=True)
    experience = Column(Text, nullable=True)
    reference = Column(Text, nullable=True)
    joining_date = Column(Date, nullable=True)
    joining_position = Column(Integer, nullable=True)

    # Job information
    designation_id = Column(Integer, ForeignKey("designations.id"), nullable=True)
    access_label = Column(SmallInteger, nullable=False)  # 1: superadmin, 2: admin, 3: hr, 4: manager, 5: employee
    role = Column(String(50), nullable=True)
    client_type_id = Column(Integer, nullable=True)

    # Bank information
    account_name = Column(String(255), nullable=True)
    bank_acc_no = Column(String(255), nullable=True)
    bank_name = Column(String(255), nullable=True)
    bank_branch = Column(String(255), nullable=True)
    bank_sort_code = Column(String(255), nullable=True)

    # Kin details
    kin_details_name = Column(String(255), nullable=True)
    kin_details_relation = Column(String(100), nullable=True)
    kin_details_phone = Column(String(30), nullable=True)

    # System fields
    avatar = Column(String(255), nullable=True)
    activation_status = Column(SmallInteger, default=0)  # 0: inactive, 1: active
    deletion_status = Column(SmallInteger, default=0)  # 0: not deleted, 1: deleted
    remember_token = Column(String(100), nullable=True)

    # Timestamps
    created_at = Column(DateTime(timezone=True), server_default=func.now())
    updated_at = Column(DateTime(timezone=True), server_default=func.now(), onupdate=func.now())

    # Relationships
    designation = relationship("Designation", back_populates="employees")
    department = relationship("Department", secondary="employee_departments", back_populates="employees")
    attendance_records = relationship("Attendance", back_populates="employee", cascade="all, delete-orphan")
    leave_requests = relationship("Leave", foreign_keys="Leave.employee_id", back_populates="employee", cascade="all, delete-orphan")
    payroll_records = relationship("Payroll", back_populates="employee", cascade="all, delete-orphan")

    @property
    def is_active(self) -> bool:
        return self.activation_status == 1 and self.deletion_status == 0

    @property
    def is_deleted(self) -> bool:
        return self.deletion_status == 1

    @property
    def display_name(self) -> str:
        prefix = self.name_prefix.upper() + " - " if self.name_prefix else ""
        return prefix + self.name

    def get_role_name(self) -> str:
        role_names = {
            1: "superadmin",
            2: "admin",
            3: "hr",
            4: "manager",
            5: "employee"
        }
        return role_names.get(self.access_label, "employee")

    def set_role(self, role_name: str):
        role_mapping = {
            "superadmin": 1,
            "admin": 2,
            "hr": 3,
            "manager": 4,
            "employee": 5
        }
        self.access_label = role_mapping.get(role_name, 5)
        self.role = role_name