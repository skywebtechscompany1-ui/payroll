# 🌱 Database Seeding Guide

## ✅ **IMPROVED SEED SCRIPT READY!**

---

## 📋 **WHAT'S INCLUDED**

### **Users (8 Total):**
1. **Super Admin** - Full system access
2. **HR Manager** - HR and employee management
3. **Accountant** - Payroll and financial management
4. **Department Manager** - Department oversight
5-8. **Employees** - Various roles and departments

### **Other Data:**
- **5 Roles** - Super Admin, HR Manager, Accountant, Manager, Employee
- **5 Departments** - IT, HR, Finance, Sales, Operations
- **10 Designations** - CEO, CTO, Developers, etc.
- **4 Leave Types** - Annual, Sick, Maternity, Paternity
- **Attendance Records** - Last 30 days (weekdays only)
- **Payroll Records** - Last 3 months with realistic salaries

---

## 🚀 **HOW TO SEED**

### **Step 1: Run the Improved Seed Script**
```bash
cd backend
python seed_data_improved.py
```

### **Expected Output:**
```
======================================================================
🌱 PAYROLL SYSTEM - DATABASE SEEDING (IMPROVED)
======================================================================

📊 Seeding Roles...
   ✓ Created 5 roles

🏢 Seeding Departments...
   ✓ Created 5 departments

🎓 Seeding Designations...
   ✓ Created 10 designations

👤 Seeding Users (Admin + Employees)...
   ✓ Created 8 users with different roles

📅 Seeding Leave Configuration...
   ✓ Created 4 leave configurations

🕐 Seeding Sample Attendance...
   ✓ Created 168 attendance records

💰 Seeding Sample Payroll...
   ✓ Created 24 payroll records

======================================================================
✅ DATABASE SEEDING COMPLETED SUCCESSFULLY!
======================================================================
```

---

## 👥 **LOGIN CREDENTIALS**

### **1. SUPER ADMIN** 🔐
```
Email: admin@jafasol.com
Password: 3r14F65gMv
Role: Super Admin
Access: Full system access
```

**Can Access:**
- ✅ All modules
- ✅ User management
- ✅ System settings
- ✅ All reports
- ✅ All CRUD operations

---

### **2. HR MANAGER** 👔
```
Email: carol.martinez@jafasol.com
Password: password123
Role: HR Manager
Access: HR and employee management
```

**Can Access:**
- ✅ Employees (create, read, update)
- ✅ Leave management (full access)
- ✅ Attendance management
- ✅ User management
- ❌ Payroll (read only)
- ❌ System settings

---

### **3. ACCOUNTANT** 💰
```
Email: david.chen@jafasol.com
Password: password123
Role: Accountant
Access: Payroll and financial management
```

**Can Access:**
- ✅ Payroll (create, read, update)
- ✅ Payments (full access)
- ✅ Reports (read)
- ❌ Employees (no access)
- ❌ Leave management
- ❌ System settings

---

### **4. DEPARTMENT MANAGER** 📊
```
Email: sarah.williams@jafasol.com
Password: password123
Role: Manager
Access: Department oversight
```

**Can Access:**
- ✅ Employees (read only)
- ✅ Leave (read, approve/reject)
- ✅ Attendance (read, update)
- ✅ Reports (read)
- ❌ Payroll (read only)
- ❌ System settings

---

### **5-8. EMPLOYEES** 👨‍💼👩‍💼

**All employees use password: `password123`**

#### **Senior Developer:**
```
Email: alice.johnson@jafasol.com
Department: IT
Designation: Senior Developer
```

#### **Junior Developer:**
```
Email: bob.williams@jafasol.com
Department: IT
Designation: Junior Developer
```

#### **Sales Executive:**
```
Email: emma.davis@jafasol.com
Department: Sales & Marketing
Designation: Sales Executive
```

#### **HR Assistant:**
```
Email: michael.brown@jafasol.com
Department: Human Resources
Designation: HR Assistant
```

**Can Access:**
- ✅ Leave (create, read own)
- ✅ Attendance (read own)
- ✅ Payslips (read own)
- ✅ Profile
- ❌ Other employees' data
- ❌ System settings

---

## 📊 **SEEDED DATA DETAILS**

### **Roles:**
| Role | Description | Permissions |
|------|-------------|-------------|
| Super Admin | Full access | All modules |
| HR Manager | HR management | Employees, Leave, Attendance, Users |
| Accountant | Financial | Payroll, Payments, Reports |
| Manager | Department oversight | Employees (read), Leave (approve), Attendance, Reports |
| Employee | Basic access | Own leave, attendance, payslips |

### **Departments:**
1. Information Technology
2. Human Resources
3. Finance & Accounting
4. Sales & Marketing
5. Operations

### **Designations:**
1. Chief Executive Officer (CEO)
2. Chief Technology Officer (CTO)
3. HR Manager
4. Senior Developer
5. Junior Developer
6. Accountant
7. Sales Executive
8. Marketing Specialist
9. Operations Manager
10. HR Assistant

### **Salary Structure:**
| Designation | Basic Salary (KES) |
|-------------|-------------------|
| CEO | 250,000 |
| CTO | 200,000 |
| HR Manager | 150,000 |
| Operations Manager | 140,000 |
| Senior Developer | 120,000 |
| Accountant | 100,000 |
| Sales Executive | 90,000 |
| Marketing Specialist | 85,000 |
| Junior Developer | 80,000 |
| HR Assistant | 70,000 |

**Allowances:**
- House Allowance: 30% of basic
- Transport Allowance: 20% of basic

**Deductions:**
- NSSF: KES 1,080
- NHIF: KES 1,700
- PAYE: 15% of gross

---

## 🧪 **TESTING ROLES**

### **Test Super Admin:**
1. Login as `admin@jafasol.com`
2. Should see all modules
3. Can access System Settings
4. Can manage all users
5. Can approve/reject leaves
6. Can process payroll

### **Test HR Manager:**
1. Login as `carol.martinez@jafasol.com`
2. Should see Employees, Leave, Attendance, Users
3. Can create/edit employees
4. Can approve/reject leaves
5. Cannot access System Settings
6. Can view but not edit payroll

### **Test Accountant:**
1. Login as `david.chen@jafasol.com`
2. Should see Payroll, Payments, Reports
3. Can process payroll
4. Can make payments
5. Cannot access employees module
6. Cannot access leave management

### **Test Manager:**
1. Login as `sarah.williams@jafasol.com`
2. Should see Employees (read), Leave, Attendance, Reports
3. Can approve/reject leave requests
4. Can view employee attendance
5. Cannot create/edit employees
6. Cannot process payroll

### **Test Employee:**
1. Login as `alice.johnson@jafasol.com`
2. Should see limited modules
3. Can apply for leave
4. Can view own attendance
5. Can view own payslips
6. Cannot access other employees' data

---

## ✅ **VERIFICATION CHECKLIST**

### **Before Seeding:**
- [ ] Database is empty or cleared
- [ ] Backend server is not running
- [ ] All migrations are up to date

### **After Seeding:**
- [ ] 8 users created
- [ ] 5 roles created
- [ ] 5 departments created
- [ ] 10 designations created
- [ ] Attendance records created
- [ ] Payroll records created

### **Login Tests:**
- [ ] Super Admin can login
- [ ] HR Manager can login
- [ ] Accountant can login
- [ ] Manager can login
- [ ] All employees can login

### **Role Tests:**
- [ ] Super Admin sees all modules
- [ ] HR Manager sees HR modules only
- [ ] Accountant sees finance modules only
- [ ] Manager sees limited modules
- [ ] Employee sees basic modules only

---

## 🔧 **TROUBLESHOOTING**

### **Issue: "Database already seeded"**
**Solution:**
```bash
# Clear database and re-seed
python manage.py db downgrade base
python manage.py db upgrade head
python seed_data_improved.py
```

### **Issue: "Cannot login"**
**Check:**
1. Password is correct (`password123` for all except admin)
2. Email is exactly as shown (case-sensitive)
3. User is marked as `is_active=True`

### **Issue: "User has no permissions"**
**Check:**
1. Role is assigned correctly
2. `access_label` matches role
3. Role has proper permissions in database

### **Issue: "Module not visible"**
**Check:**
1. User's role has module in `module_access`
2. Frontend checks `auth.hasPermission()` correctly
3. Navigation is configured for that role

---

## 📝 **KEY DIFFERENCES FROM OLD SEED SCRIPT**

### **Improvements:**
1. ✅ **More users** - 8 instead of 5
2. ✅ **More roles** - 5 instead of 4 (added Manager role)
3. ✅ **Realistic salaries** - Based on designation
4. ✅ **Proper role assignment** - Each user has correct role
5. ✅ **Better permissions** - More granular access control
6. ✅ **Common password** - Easy testing with `password123`
7. ✅ **More leave types** - 4 instead of 3
8. ✅ **Better output** - Shows all credentials at end

### **What's Fixed:**
1. ✅ Uses `password` field (not `hashed_password`)
2. ✅ All required User model fields included
3. ✅ Proper gender field (`M` or `F`)
4. ✅ Present address included (required field)
5. ✅ Contact number included (required field)

---

## 🎯 **RECOMMENDED TESTING FLOW**

### **Day 1: Authentication**
1. Test all user logins
2. Verify password reset works
3. Check remember me functionality

### **Day 2: Role-Based Access**
1. Login as each role
2. Verify visible modules
3. Test permission restrictions

### **Day 3: Core Features**
1. Create employees (as HR)
2. Apply for leave (as Employee)
3. Approve leave (as Manager/HR)
4. Process payroll (as Accountant)

### **Day 4: Advanced Features**
1. Generate reports
2. Upload system logo
3. Configure leave settings
4. Test designation status toggle

---

## 🎉 **READY TO TEST!**

**Run the seed script and start testing with different user roles!**

```bash
cd backend
python seed_data_improved.py
```

**Then login at:** http://localhost:3000/login

**Try each user to see how roles work!** 🚀

---

**Made by Jafasol Systems | Copyright © 2014-2051 PAYROLL. All rights reserved.**
