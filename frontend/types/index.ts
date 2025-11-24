// Global type definitions

export interface User {
  id: number
  email: string
  name: string
  role: string
  employee_id?: string
  is_active: boolean
  access_label: number
  designation_id?: number
  created_at: string
  updated_at: string
}

export interface Department {
  id: number
  department: string
  department_description?: string
  publication_status: number
  deletion_status: number
  created_at: string
  updated_at: string
}

export interface Designation {
  id: number
  department_id: number
  designation: string
  designation_description?: string
  publication_status: number
  deletion_status: number
  created_at: string
  updated_at: string
}

export interface Employee {
  id: number
  name: string
  email?: string
  employee_id?: string
  present_address: string
  contact_no_one: string
  gender: string
  date_of_birth?: string
  joining_date?: string
  designation_id?: number
  role?: string
  access_label: number
  activation_status: number
  avatar?: string
  bank_acc_no?: string
  bank_name?: string
  created_at: string
  updated_at: string
}

export interface ApiResponse<T = any> {
  success: boolean
  data?: T
  error?: string
  message?: string
}

export interface PaginatedResponse<T> {
  data: T[]
  total: number
  page: number
  page_size: number
  pages: number
}

export interface ChartData {
  labels: string[]
  datasets: {
    label: string
    data: number[]
    borderColor?: string
    backgroundColor?: string | string[]
    tension?: number
  }[]
}

export interface Activity {
  id: number
  description: string
  type: 'success' | 'warning' | 'error' | 'info'
  timestamp: string
  user_id?: number
}

export interface Notification {
  id: number
  title: string
  message: string
  type: 'info' | 'success' | 'warning' | 'error'
  read: boolean
  created_at: string
}

export interface SelectOption {
  value: string | number
  label: string
  disabled?: boolean
}

export interface TableColumn {
  key: string
  label: string
  sortable?: boolean
  width?: string
  align?: 'left' | 'center' | 'right'
}

export interface TableAction {
  label: string
  icon?: string
  action: (item: any) => void
  disabled?: (item: any) => boolean
  variant?: 'primary' | 'secondary' | 'success' | 'warning' | 'error'
}

export interface FilterOption {
  key: string
  label: string
  type: 'text' | 'select' | 'date' | 'daterange'
  options?: SelectOption[]
}

export interface FormField {
  name: string
  label: string
  type: 'text' | 'email' | 'password' | 'select' | 'textarea' | 'date' | 'number'
  required?: boolean
  placeholder?: string
  options?: SelectOption[]
  validation?: {
    min?: number
    max?: number
    pattern?: string
    message?: string
  }
}

export interface PayrollPeriod {
  id: number
  name: string
  start_date: string
  end_date: string
  status: 'draft' | 'processing' | 'completed'
  created_at: string
}

export interface Payslip {
  id: number
  employee_id: number
  payroll_period_id: number
  basic_salary: number
  allowances: number
  deductions: number
  gross_pay: number
  net_pay: number
  created_at: string
}

export interface LeaveApplication {
  id: number
  employee_id: number
  leave_type: string
  start_date: string
  end_date: string
  days: number
  reason: string
  status: 'pending' | 'approved' | 'rejected'
  approved_by?: number
  created_at: string
}

export interface AttendanceRecord {
  id: number
  employee_id: number
  date: string
  clock_in?: string
  clock_out?: string
  hours_worked?: number
  status: 'present' | 'absent' | 'late' | 'half_day'
  created_at: string
}

export interface ReportTemplate {
  id: number
  name: string
  description?: string
  type: string
  fields: string[]
  created_at: string
}

export interface SystemSettings {
  company_name: string
  company_address?: string
  company_phone?: string
  company_email?: string
  currency: string
  tax_rates: {
    paye: number
    nssf: number
    nhif: number
  }
  working_days: {
    monday: boolean
    tuesday: boolean
    wednesday: boolean
    thursday: boolean
    friday: boolean
    saturday: boolean
    sunday: boolean
  }
}