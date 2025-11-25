"""
IMPROVED Database Seeding Script for Payroll System
Seeds initial data with proper user credentials for testing roles
All users can login with password: "password123"
"""

import sys
import os
from datetime import datetime, timedelta
from decimal import Decimal

# Add parent directory to path
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from sqlalchemy.orm import Session
from app.core.database import SessionLocal, engine
from app.models import Base
from app.models.user import User
from app.models.department import Department
from app.models.designation import Designation
from app.models.role import Role
from app.models.attendance import Attendance
from app.models.leave import Leave
from app.models.payroll import Payroll
from app.models.leave_config import LeaveConfig
from app.core.security import get_password_hash

def seed_database():
    """Main seeding function"""
    print("=" * 70)
    print("🌱 PAYROLL SYSTEM - DATABASE SEEDING (IMPROVED)")
    print("=" * 70)
    
    # Create tables
    Base.metadata.create_all(bind=engine)
    
    db = SessionLocal()
    
    try:
        # Check if data already exists
        existing_roles = db.query(Role).count()
        if existing_roles > 0:
            print("\n⚠️  Database already contains data!")
            print("   To re-seed, please clear the database first.")
            print(f"   Found {existing_roles} roles in database.")
            return
        
        print("\n📊 Seeding Roles...")
        seed_roles(db)
        
        print("\n🏢 Seeding Departments...")
        seed_departments(db)
        
        print("\n🎓 Seeding Designations...")
        seed_designations(db)
        
        print("\n👤 Seeding Users (Admin + Employees)...")
        seed_users(db)
        
        print("\n📅 Seeding Leave Configuration...")
        seed_leave_config(db)
        
        print("\n🕐 Seeding Sample Attendance...")
        seed_attendance(db)
        
        print("\n💰 Seeding Sample Payroll...")
        seed_payroll(db)
        
        print("\n" + "=" * 70)
        print("✅ DATABASE SEEDING COMPLETED SUCCESSFULLY!")
        print("=" * 70)
        
    except Exception as e:
        print(f"\n❌ Error during seeding: {str(e)}")
        import traceback
        traceback.print_exc()
        db.rollback()
        raise
    finally:
        db.close()

def seed_roles(db: Session):
    """Seed user roles"""
    roles = [
        {
            "name": "Super Admin",
            "display_name": "Super Administrator",
            "description": "Full system access with all permissions",
            "is_system": True,
            "permissions": {
                "users": {"create": True, "read": True, "update": True, "delete": True},
                "leave": {"create": True, "read": True, "update": True, "delete": True},
                "payroll": {"create": True, "read": True, "update": True, "delete": True},
                "reports": {"create": True, "read": True, "update": True, "delete": True}
            }
        },
        {
            "name": "HR Manager",
            "display_name": "Human Resources Manager",
            "description": "Manages employees, leave, and attendance",
            "is_system": True,
            "permissions": {
                "employees": {"create": True, "read": True, "update": True, "delete": True},
                "leave": {"create": True, "read": True, "update": True, "delete": True},
                "attendance": {"create": True, "read": True, "update": True, "delete": True},
                "reports": {"read": True}
            }
        },
        {
            "name": "Accountant",
            "display_name": "Accountant",
            "description": "Manages payroll and financial operations",
            "is_system": True,
            "permissions": {
                "payroll": {"create": True, "read": True, "update": True, "delete": True},
                "payments": {"create": True, "read": True, "update": True},
                "reports": {"read": True}
            }
        },
        {
            "name": "Manager",
            "display_name": "Department Manager",
            "description": "Manages team members and approves leave",
            "is_system": False,
            "permissions": {
                "employees": {"read": True},
                "leave": {"read": True, "update": True},
                "attendance": {"read": True, "update": True},
                "reports": {"read": True}
            }
        },
        {
            "name": "Employee",
            "display_name": "Employee",
            "description": "Basic employee access",
            "is_system": True,
            "permissions": {
                "leave": {"create": True, "read": True},
                "attendance": {"read": True},
                "payslips": {"read": True}
            }
        }
    ]
    
    for role_data in roles:
        role = Role(**role_data)
        db.add(role)
    
    db.commit()
    print(f"   ✓ Created {len(roles)} roles")

def seed_departments(db: Session):
    """Seed departments"""
    departments = [
        {"department": "Information Technology", "created_by": 1, "department_description": "Technology and IT services"},
        {"department": "Human Resources", "created_by": 1, "department_description": "HR and employee management"},
        {"department": "Finance & Accounting", "created_by": 1, "department_description": "Financial operations"},
        {"department": "Sales & Marketing", "created_by": 1, "department_description": "Sales and marketing activities"},
        {"department": "Operations", "created_by": 1, "department_description": "Business operations"}
    ]
    
    for dept_data in departments:
        dept = Department(**dept_data)
        db.add(dept)
    
    db.commit()
    print(f"   ✓ Created {len(departments)} departments")

def seed_designations(db: Session):
    """Seed designations"""
    designations = [
        {"designation": "Chief Executive Officer", "department_id": 5, "designation_description": "Top executive", "publication_status": 1, "created_by": 1},
        {"designation": "Chief Technology Officer", "department_id": 1, "designation_description": "Technology leader", "publication_status": 1, "created_by": 1},
        {"designation": "HR Manager", "department_id": 2, "designation_description": "Human resources management", "publication_status": 1, "created_by": 1},
        {"designation": "Senior Developer", "department_id": 1, "designation_description": "Software development", "publication_status": 1, "created_by": 1},
        {"designation": "Junior Developer", "department_id": 1, "designation_description": "Entry-level development", "publication_status": 1, "created_by": 1},
        {"designation": "Accountant", "department_id": 3, "designation_description": "Financial management", "publication_status": 1, "created_by": 1},
        {"designation": "Sales Executive", "department_id": 4, "designation_description": "Sales and client relations", "publication_status": 1, "created_by": 1},
        {"designation": "Marketing Specialist", "department_id": 4, "designation_description": "Marketing campaigns", "publication_status": 1, "created_by": 1},
        {"designation": "Operations Manager", "department_id": 5, "designation_description": "Operations oversight", "publication_status": 1, "created_by": 1},
        {"designation": "HR Assistant", "department_id": 2, "designation_description": "HR support", "publication_status": 1, "created_by": 1}
    ]
    
    for desig_data in designations:
        desig = Designation(**desig_data)
        db.add(desig)
    
    db.commit()
    print(f"   ✓ Created {len(designations)} designations")

def seed_users(db: Session):
    """Seed all users with proper roles"""
    
    # Get roles
    super_admin_role = db.query(Role).filter(Role.name == "Super Admin").first()
    hr_role = db.query(Role).filter(Role.name == "HR Manager").first()
    accountant_role = db.query(Role).filter(Role.name == "Accountant").first()
    manager_role = db.query(Role).filter(Role.name == "Manager").first()
    employee_role = db.query(Role).filter(Role.name == "Employee").first()
    
    # All users will have password: "password123" (except admin)
    common_password = get_password_hash("password123")
    admin_password = get_password_hash("3r14F65gMv")
    
    users = [
        # 1. Super Admin
        {
            "name": "System Administrator",
            "email": "admin@jafasol.com",
            "password": admin_password,
            "employee_id": "EMP001",
            "designation_id": 1,  # CEO
            "access_label": 1,    # Super Admin
            "role": "superadmin",
            "activation_status": 1,  # Active
            "deletion_status": 0,    # Not deleted
            "gender": "M",
            "present_address": "Nairobi, Kenya",
            "contact_no_one": "+254 700 000 000",
            "joining_date": (datetime.now() - timedelta(days=1825)).date()
        },
        # 2. HR Manager
        {
            "name": "Carol Martinez",
            "email": "carol.martinez@jafasol.com",
            "password": common_password,
            "employee_id": "EMP002",
            "designation_id": 3,  # HR Manager
            "access_label": 3,    # HR
            "role": "hr",
            "activation_status": 1,  # Active
            "deletion_status": 0,    # Not deleted
            "gender": "F",
            "present_address": "Nairobi, Kenya",
            "contact_no_one": "+254 712 345 678",
            "joining_date": (datetime.now() - timedelta(days=1095)).date()
        },
        # 3. Accountant
        {
            "name": "David Chen",
            "email": "david.chen@jafasol.com",
            "password": common_password,
            "employee_id": "EMP003",
            "designation_id": 6,  # Accountant
            "access_label": 4,    # Employee (but with Accountant role)
            "role": "accountant",
            "activation_status": 1,  # Active
            "deletion_status": 0,    # Not deleted
            "gender": "M",
            "present_address": "Nairobi, Kenya",
            "contact_no_one": "+254 723 456 789",
            "joining_date": (datetime.now() - timedelta(days=730)).date()
        },
        # 4. Department Manager
        {
            "name": "Sarah Williams",
            "email": "sarah.williams@jafasol.com",
            "password": common_password,
            "employee_id": "EMP004",
            "designation_id": 9,  # Operations Manager
            "access_label": 2,    # Manager
            "role": "manager",
            "activation_status": 1,  # Active
            "deletion_status": 0,    # Not deleted
            "gender": "F",
            "present_address": "Nairobi, Kenya",
            "contact_no_one": "+254 734 567 890",
            "joining_date": (datetime.now() - timedelta(days=900)).date()
        },
        # 5. Senior Developer
        {
            "name": "Alice Johnson",
            "email": "alice.johnson@jafasol.com",
            "password": common_password,
            "employee_id": "EMP005",
            "designation_id": 4,  # Senior Developer
            "access_label": 4,    # Employee
            "role": "employee",
            "activation_status": 1,  # Active
            "deletion_status": 0,    # Not deleted
            "gender": "F",
            "present_address": "Nairobi, Kenya",
            "contact_no_one": "+254 745 678 901",
            "joining_date": (datetime.now() - timedelta(days=600)).date()
        },
        # 6. Junior Developer
        {
            "name": "Bob Williams",
            "email": "bob.williams@jafasol.com",
            "password": common_password,
            "employee_id": "EMP006",
            "designation_id": 5,  # Junior Developer
            "access_label": 4,    # Employee
            "role": "employee",
            "activation_status": 1,  # Active
            "deletion_status": 0,    # Not deleted
            "gender": "M",
            "present_address": "Nairobi, Kenya",
            "contact_no_one": "+254 756 789 012",
            "joining_date": (datetime.now() - timedelta(days=365)).date()
        },
        # 7. Sales Executive
        {
            "name": "Emma Davis",
            "email": "emma.davis@jafasol.com",
            "password": common_password,
            "employee_id": "EMP007",
            "designation_id": 7,  # Sales Executive
            "access_label": 4,    # Employee
            "role": "employee",
            "activation_status": 1,  # Active
            "deletion_status": 0,    # Not deleted
            "gender": "F",
            "present_address": "Nairobi, Kenya",
            "contact_no_one": "+254 767 890 123",
            "joining_date": (datetime.now() - timedelta(days=200)).date()
        },
        # 8. HR Assistant
        {
            "name": "Michael Brown",
            "email": "michael.brown@jafasol.com",
            "password": common_password,
            "employee_id": "EMP008",
            "designation_id": 10, # HR Assistant
            "access_label": 4,    # Employee
            "role": "employee",
            "activation_status": 1,  # Active
            "deletion_status": 0,    # Not deleted
            "gender": "M",
            "present_address": "Nairobi, Kenya",
            "contact_no_one": "+254 778 901 234",
            "joining_date": (datetime.now() - timedelta(days=150)).date()
        }
    ]
    
    for user_data in users:
        user = User(**user_data)
        db.add(user)
    
    db.commit()
    print(f"   ✓ Created {len(users)} users with different roles")

def seed_leave_config(db: Session):
    """Seed leave configuration"""
    leave_configs = [
        {
            "leave_type": 3,  # Annual
            "leave_type_name": "Annual Leave",
            "annual_days": 21,
            "can_carry_forward": True,
            "max_carry_forward_days": 5,
            "requires_approval": True,
            "is_paid": True,
            "is_active": True
        },
        {
            "leave_type": 1,  # Sick
            "leave_type_name": "Sick Leave",
            "annual_days": 10,
            "can_carry_forward": False,
            "max_carry_forward_days": 0,
            "requires_approval": True,
            "is_paid": True,
            "is_active": True
        },
        {
            "leave_type": 4,  # Maternity
            "leave_type_name": "Maternity Leave",
            "annual_days": 90,
            "can_carry_forward": False,
            "max_carry_forward_days": 0,
            "requires_approval": True,
            "is_paid": True,
            "is_active": True
        },
        {
            "leave_type": 5,  # Paternity
            "leave_type_name": "Paternity Leave",
            "annual_days": 14,
            "can_carry_forward": False,
            "max_carry_forward_days": 0,
            "requires_approval": True,
            "is_paid": True,
            "is_active": True
        }
    ]
    
    for config_data in leave_configs:
        config = LeaveConfig(**config_data)
        db.add(config)
    
    db.commit()
    print(f"   ✓ Created {len(leave_configs)} leave configurations")

def seed_attendance(db: Session):
    """Seed sample attendance for last 30 days"""
    employees = db.query(User).filter(User.employee_id.isnot(None)).all()
    
    attendance_count = 0
    for employee in employees:
        for days_ago in range(30):
            date = datetime.now().date() - timedelta(days=days_ago)
            
            # Skip weekends
            if date.weekday() >= 5:
                continue
            
            attendance = Attendance(
                employee_id=employee.id,
                date=date,
                clock_in=datetime.strptime("09:00", "%H:%M").time(),
                clock_out=datetime.strptime("17:00", "%H:%M").time(),
                status=1  # Present
            )
            db.add(attendance)
            attendance_count += 1
    
    db.commit()
    print(f"   ✓ Created {attendance_count} attendance records")

def seed_payroll(db: Session):
    """Seed sample payroll for last 3 months"""
    employees = db.query(User).filter(User.employee_id.isnot(None)).all()
    
    # Salary structure based on designation
    salary_map = {
        1: 250000,  # CEO
        2: 200000,  # CTO
        3: 150000,  # HR Manager
        4: 120000,  # Senior Developer
        5: 80000,   # Junior Developer
        6: 100000,  # Accountant
        7: 90000,   # Sales Executive
        8: 85000,   # Marketing Specialist
        9: 140000,  # Operations Manager
        10: 70000   # HR Assistant
    }
    
    payroll_count = 0
    for employee in employees:
        basic_salary = Decimal(str(salary_map.get(employee.designation_id, 80000)))
        
        for months_ago in range(3):
            date = datetime.now() - timedelta(days=30 * months_ago)
            month = date.month
            year = date.year
            
            house_allowance = basic_salary * Decimal("0.3")
            transport_allowance = basic_salary * Decimal("0.2")
            gross_salary = basic_salary + house_allowance + transport_allowance
            
            nssf = Decimal("1080.00")
            nhif = Decimal("1700.00")
            paye = gross_salary * Decimal("0.15")
            total_deductions = nssf + nhif + paye
            
            net_salary = gross_salary - total_deductions
            
            payroll = Payroll(
                employee_id=employee.id,
                month=month,
                year=year,
                basic_salary=basic_salary,
                house_allowance=house_allowance,
                transport_allowance=transport_allowance,
                gross_salary=gross_salary,
                nssf=nssf,
                nhif=nhif,
                paye=paye,
                total_deductions=total_deductions,
                net_salary=net_salary,
                status=3,  # Paid
                working_days=Decimal("22.0"),
                days_worked=Decimal("22.0")
            )
            db.add(payroll)
            payroll_count += 1
    
    db.commit()
    print(f"   ✓ Created {payroll_count} payroll records")

if __name__ == "__main__":
    seed_database()
    
    print("\n" + "=" * 70)
    print("📋 LOGIN CREDENTIALS")
    print("=" * 70)
    print("\n1. SUPER ADMIN:")
    print("   Email: admin@jafasol.com")
    print("   Password: 3r14F65gMv")
    print("   Role: Full system access")
    
    print("\n2. HR MANAGER:")
    print("   Email: carol.martinez@jafasol.com")
    print("   Password: password123")
    print("   Role: HR and employee management")
    
    print("\n3. ACCOUNTANT:")
    print("   Email: david.chen@jafasol.com")
    print("   Password: password123")
    print("   Role: Payroll and financial management")
    
    print("\n4. DEPARTMENT MANAGER:")
    print("   Email: sarah.williams@jafasol.com")
    print("   Password: password123")
    print("   Role: Department oversight")
    
    print("\n5. EMPLOYEES (All use password: password123):")
    print("   - alice.johnson@jafasol.com (Senior Developer)")
    print("   - bob.williams@jafasol.com (Junior Developer)")
    print("   - emma.davis@jafasol.com (Sales Executive)")
    print("   - michael.brown@jafasol.com (HR Assistant)")
    
    print("\n" + "=" * 70)
    print("🎉 You can now login with any of these users to test roles!")
    print("=" * 70)
