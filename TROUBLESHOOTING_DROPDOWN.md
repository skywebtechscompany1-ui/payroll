# 🔧 Troubleshooting Dropdown Menu

## ✅ **Fixes Applied:**

### **1. Fixed Duplicate Employees** ✅
- Added check to prevent duplicate "Employees" menu item
- Now only shows once even if user is both Admin and HR

### **2. Dropdown Structure** ✅
The code now has:
- **Payroll** dropdown with 5 children
- **Settings** dropdown with 3 children
- Chevron indicator (▶/▼) on the right side

---

## 🔍 **What You Should See:**

### **In Your Sidebar:**

```
🏠 Dashboard
👤 Profile
👥 Users (Admin)
💼 Employees
🏢 Departments
🎓 Designations
🕐 Attendance
📅 Leave

💵 Payroll ▼              ← This is a BUTTON (clickable)
   📊 Overview
   💰 Manage Salary
   💳 Payments
   📄 Payslips
   📊 Reports

⚙️ Settings ▼             ← This is a BUTTON (clickable)
   🛡️ Roles
   📋 Activity Logs
   ⚙️ Leave Config
```

---

## 🎯 **If You Still Can't See Them:**

### **Check 1: Browser Console**
Open browser console (F12) and check for errors

### **Check 2: Refresh**
Hard refresh: **Ctrl+Shift+R** (Windows) or **Cmd+Shift+R** (Mac)

### **Check 3: Check Auth Permissions**
The dropdowns only show if:
- **Payroll dropdown** → User has `payroll:read` permission
- **Settings dropdown** → User is Admin OR HR

### **Check 4: Scroll in Sidebar**
If sidebar is too small, you might need to scroll down to see all items

---

## 🎨 **Visual Indicators:**

### **Dropdown Button Appearance:**
- Has **icon on left** (💵 or ⚙️)
- Has **text in middle** (Payroll or Settings)
- Has **chevron on right** (▶ when closed, ▼ when open)
- **Entire row is clickable** (it's a button)

### **When Expanded:**
- Chevron changes from ▶ to ▼
- Child items appear indented below
- Child items have smaller icons
- Child items are clickable links

---

## 🚀 **Quick Test:**

1. **Look for "Payroll"** in sidebar
   - Should have 💵 icon
   - Should have ▼ chevron on right
   - Should be expanded by default

2. **Look for "Settings"** in sidebar
   - Should have ⚙️ icon
   - Should have ▼ chevron on right
   - Should be expanded by default

3. **Click "Payroll"**
   - Should collapse (chevron changes to ▶)
   - Child items disappear

4. **Click "Payroll" again**
   - Should expand (chevron changes to ▼)
   - Child items reappear

---

## 💡 **Current State:**

### **Dropdowns are set to:**
- ✅ **Auto-open by default** (both Payroll and Settings)
- ✅ **Show chevron indicator**
- ✅ **Highlight when active**
- ✅ **Toggle on click**

### **Code Location:**
- File: `frontend/layouts/default.vue`
- Lines 228: `const openDropdowns = ref<string[]>(['Payroll', 'Settings'])`
- Lines 279-290: Payroll dropdown definition
- Lines 293-303: Settings dropdown definition

---

## 🔍 **Debug Steps:**

If you still can't see the dropdowns:

1. **Open browser DevTools** (F12)
2. **Go to Console tab**
3. **Type:** `console.log(document.querySelectorAll('.sidebar-item'))`
4. **Press Enter**
5. **Check how many items are found**

You should see multiple elements. The dropdown buttons should be among them.

---

## ✅ **Expected Behavior:**

### **On Page Load:**
- Sidebar shows all menu items
- "Payroll" appears as a button with ▼
- 5 child items visible below Payroll (indented)
- "Settings" appears as a button with ▼
- 3 child items visible below Settings (indented)

### **When Clicking Payroll:**
- Chevron rotates (▼ → ▶)
- Child items slide up/disappear
- Button stays visible

### **When Clicking Again:**
- Chevron rotates (▶ → ▼)
- Child items slide down/appear
- Button stays visible

---

## 📸 **What It Should Look Like:**

```
┌─────────────────────────────────┐
│  PAYROLL SYSTEM                 │
├─────────────────────────────────┤
│                                 │
│  🏠 Dashboard                   │
│  👤 Profile                     │
│                                 │
│  👥 Users (Admin)               │
│  💼 Employees                   │
│  🏢 Departments                 │
│  🎓 Designations                │
│                                 │
│  🕐 Attendance                  │
│  📅 Leave                       │
│                                 │
│  💵 Payroll              ▼      │ ← BUTTON
│     📊 Overview                 │ ← Link (indented)
│     💰 Manage Salary            │ ← Link (indented)
│     💳 Payments                 │ ← Link (indented)
│     📄 Payslips                 │ ← Link (indented)
│     📊 Reports                  │ ← Link (indented)
│                                 │
│  ⚙️ Settings             ▼      │ ← BUTTON
│     🛡️ Roles                    │ ← Link (indented)
│     📋 Activity Logs            │ ← Link (indented)
│     ⚙️ Leave Config             │ ← Link (indented)
│                                 │
└─────────────────────────────────┘
```

---

## 🎯 **Key Points:**

1. ✅ **Duplicate Employees fixed**
2. ✅ **Dropdown buttons have chevron indicators**
3. ✅ **Both dropdowns open by default**
4. ✅ **Click to toggle expand/collapse**
5. ✅ **Child items are indented and smaller**

**If you still can't see them, please:**
- Take a screenshot of your sidebar
- Check browser console for errors
- Verify you're logged in with proper permissions
