"""
Comprehensive input validation utilities
"""
import re
from typing import Optional
from pydantic import validator
from fastapi import UploadFile, HTTPException
import magic  # python-magic for file type detection


class ValidationError(Exception):
    """Custom validation error"""
    pass


# Email validation
def validate_email(email: str) -> str:
    """Validate email format"""
    pattern = r'^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$'
    if not re.match(pattern, email):
        raise ValidationError("Invalid email format")
    return email.lower()


# Phone validation
def validate_phone(phone: str) -> str:
    """Validate phone number (international format)"""
    # Remove spaces and dashes
    cleaned = re.sub(r'[\s\-\(\)]', '', phone)
    
    # Check if it's a valid phone number (10-15 digits)
    if not re.match(r'^\+?[1-9]\d{9,14}$', cleaned):
        raise ValidationError("Invalid phone number format")
    
    return cleaned


# Employee ID validation
def validate_employee_id(employee_id: str) -> str:
    """Validate employee ID format (alphanumeric, 6-12 chars)"""
    if not re.match(r'^[A-Z0-9]{6,12}$', employee_id.upper()):
        raise ValidationError("Employee ID must be 6-12 alphanumeric characters")
    return employee_id.upper()


# Password strength validation
def validate_password_strength(password: str) -> str:
    """Validate password strength"""
    if len(password) < 8:
        raise ValidationError("Password must be at least 8 characters long")
    
    if not re.search(r'[A-Z]', password):
        raise ValidationError("Password must contain at least one uppercase letter")
    
    if not re.search(r'[a-z]', password):
        raise ValidationError("Password must contain at least one lowercase letter")
    
    if not re.search(r'\d', password):
        raise ValidationError("Password must contain at least one digit")
    
    if not re.search(r'[!@#$%^&*(),.?":{}|<>]', password):
        raise ValidationError("Password must contain at least one special character")
    
    return password


# File upload validation
ALLOWED_IMAGE_TYPES = {'image/jpeg', 'image/png', 'image/gif', 'image/webp'}
ALLOWED_DOCUMENT_TYPES = {'application/pdf', 'application/msword', 
                          'application/vnd.openxmlformats-officedocument.wordprocessingml.document'}
ALLOWED_SPREADSHEET_TYPES = {'application/vnd.ms-excel', 
                             'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                             'text/csv'}

MAX_FILE_SIZE = 10 * 1024 * 1024  # 10MB


async def validate_file_upload(
    file: UploadFile,
    allowed_types: set = None,
    max_size: int = MAX_FILE_SIZE
) -> UploadFile:
    """
    Validate uploaded file
    
    Args:
        file: Uploaded file
        allowed_types: Set of allowed MIME types
        max_size: Maximum file size in bytes
    
    Returns:
        Validated file
    
    Raises:
        HTTPException: If validation fails
    """
    # Check if file exists
    if not file:
        raise HTTPException(status_code=400, detail="No file provided")
    
    # Check file size
    content = await file.read()
    file_size = len(content)
    
    if file_size > max_size:
        raise HTTPException(
            status_code=400,
            detail=f"File too large. Maximum size is {max_size / 1024 / 1024}MB"
        )
    
    # Reset file pointer
    await file.seek(0)
    
    # Check file type by extension
    if allowed_types:
        file_ext = file.filename.split('.')[-1].lower() if file.filename else ''
        
        # Validate MIME type
        try:
            mime = magic.from_buffer(content, mime=True)
            if mime not in allowed_types:
                raise HTTPException(
                    status_code=400,
                    detail=f"Invalid file type. Allowed types: {', '.join(allowed_types)}"
                )
        except:
            # Fallback to extension check
            allowed_extensions = {
                'jpg', 'jpeg', 'png', 'gif', 'webp',  # Images
                'pdf', 'doc', 'docx',  # Documents
                'xls', 'xlsx', 'csv'  # Spreadsheets
            }
            if file_ext not in allowed_extensions:
                raise HTTPException(
                    status_code=400,
                    detail=f"Invalid file extension: .{file_ext}"
                )
    
    # Sanitize filename
    if file.filename:
        # Remove dangerous characters
        safe_filename = re.sub(r'[^\w\s\-\.]', '', file.filename)
        file.filename = safe_filename
    
    return file


# Salary validation
def validate_salary(salary: float) -> float:
    """Validate salary amount"""
    if salary < 0:
        raise ValidationError("Salary cannot be negative")
    
    if salary > 10000000:  # 10 million max
        raise ValidationError("Salary amount too high")
    
    return round(salary, 2)


# Date range validation
from datetime import date, datetime

def validate_date_range(start_date: date, end_date: date) -> tuple:
    """Validate date range"""
    if start_date > end_date:
        raise ValidationError("Start date must be before end date")
    
    if start_date < date(2000, 1, 1):
        raise ValidationError("Start date too far in the past")
    
    if end_date > date.today().replace(year=date.today().year + 10):
        raise ValidationError("End date too far in the future")
    
    return start_date, end_date


# Leave days validation
def validate_leave_days(days: int, max_days: int = 365) -> int:
    """Validate leave days"""
    if days < 0:
        raise ValidationError("Leave days cannot be negative")
    
    if days > max_days:
        raise ValidationError(f"Leave days cannot exceed {max_days}")
    
    return days


# Working hours validation
def validate_working_hours(hours: float) -> float:
    """Validate working hours"""
    if hours < 0:
        raise ValidationError("Working hours cannot be negative")
    
    if hours > 24:
        raise ValidationError("Working hours cannot exceed 24 hours per day")
    
    return round(hours, 2)


# Sanitize HTML input (prevent XSS)
def sanitize_html(text: str) -> str:
    """Remove HTML tags and dangerous characters"""
    if not text:
        return text
    
    # Remove HTML tags
    clean = re.sub(r'<[^>]+>', '', text)
    
    # Remove script tags content
    clean = re.sub(r'<script.*?</script>', '', clean, flags=re.DOTALL)
    
    # Remove dangerous characters
    clean = clean.replace('<', '&lt;').replace('>', '&gt;')
    
    return clean.strip()


# SQL injection prevention (for raw queries)
def sanitize_sql_input(text: str) -> str:
    """Sanitize input to prevent SQL injection"""
    if not text:
        return text
    
    # Remove SQL keywords and dangerous characters
    dangerous_patterns = [
        r'(\bDROP\b|\bDELETE\b|\bUPDATE\b|\bINSERT\b|\bEXEC\b|\bUNION\b)',
        r'(--|;|\'|\"|\*|\/\*|\*\/)'
    ]
    
    clean = text
    for pattern in dangerous_patterns:
        clean = re.sub(pattern, '', clean, flags=re.IGNORECASE)
    
    return clean.strip()
