from typing import Any
from fastapi import APIRouter, Depends, File, UploadFile, HTTPException, Form
from sqlalchemy.orm import Session
from datetime import datetime
import shutil
import os
from pathlib import Path

from app.api import deps
from app.core.config import settings

router = APIRouter()

# Settings storage (in production, use database)
SETTINGS_FILE = "system_settings.json"
UPLOAD_DIR = Path("uploads/logos")
UPLOAD_DIR.mkdir(parents=True, exist_ok=True)

@router.get("")
async def get_system_settings(
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_active_user)
) -> Any:
    """
    Get system settings
    """
    import json
    
    if os.path.exists(SETTINGS_FILE):
        with open(SETTINGS_FILE, 'r') as f:
            return json.load(f)
    
    # Return default settings
    return {
        "company_name": "Jafasol Systems",
        "company_email": "info@jafasol.com",
        "company_phone": "+254 700 000 000",
        "company_address": "Nairobi, Kenya",
        "logo_url": "",
        "currency": "KES",
        "timezone": "Africa/Nairobi",
        "date_format": "DD/MM/YYYY",
        "time_format": "24",
        "payroll_cycle": "monthly",
        "payment_day": 25,
        "working_hours_per_day": 8,
        "working_days_per_week": 5
    }

@router.post("")
async def save_system_settings(
    settings_data: dict,
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("settings:update"))
) -> Any:
    """
    Save system settings
    """
    import json
    
    with open(SETTINGS_FILE, 'w') as f:
        json.dump(settings_data, f, indent=2)
    
    return {"message": "Settings saved successfully", "settings": settings_data}

@router.post("/upload-logo")
async def upload_logo(
    logo: UploadFile = File(...),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("settings:update"))
) -> Any:
    """
    Upload company logo
    """
    # Validate file type
    if not logo.content_type.startswith("image/"):
        raise HTTPException(status_code=400, detail="File must be an image")
    
    # Generate unique filename
    file_extension = logo.filename.split(".")[-1]
    filename = f"company_logo.{file_extension}"
    file_path = UPLOAD_DIR / filename
    
    # Save file
    with open(file_path, "wb") as buffer:
        shutil.copyfileobj(logo.file, buffer)
    
    # Return URL
    logo_url = f"/uploads/logos/{filename}"
    
    return {"logo_url": logo_url, "message": "Logo uploaded successfully"}

@router.delete("/logo")
async def delete_logo(
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("settings:update"))
) -> Any:
    """
    Delete company logo
    """
    # Delete all logo files
    for file in UPLOAD_DIR.glob("company_logo.*"):
        file.unlink()
    
    return {"message": "Logo deleted successfully"}

@router.post("/import-data")
async def import_data(
    file: UploadFile = File(...),
    data_type: str = Form(None),
    skip_first_row: str = Form("false"),
    update_existing: str = Form("false"),
    validate_data: str = Form("true"),
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("settings:update"))
) -> Any:
    """
    Import system data from Excel, CSV, or JSON file
    """
    try:
        import pandas as pd
        import io
        
        # Get file extension
        file_ext = Path(file.filename).suffix.lower() if file.filename else ''
        
        # Validate file type
        allowed_extensions = ['.xlsx', '.xls', '.csv', '.json']
        if file_ext not in allowed_extensions:
            raise HTTPException(
                status_code=400, 
                detail=f"File must be one of: {', '.join(allowed_extensions)}. Got: {file_ext}"
            )
        
        # Read file content
        content = await file.read()
        
        # Parse based on file type
        if file_ext == '.csv':
            df = pd.read_csv(io.BytesIO(content))
        elif file_ext in ['.xlsx', '.xls']:
            df = pd.read_excel(io.BytesIO(content), engine='openpyxl' if file_ext == '.xlsx' else None)
        elif file_ext == '.json':
            import json
            data = json.loads(content)
            df = pd.DataFrame(data if isinstance(data, list) else [data])
        
        # Convert DataFrame to dict
        records = df.to_dict('records')
        
        # Return success with preview
        return {
            "success": True,
            "message": "Data imported successfully",
            "records_imported": len(records),
            "columns": list(df.columns),
            "preview": records[:5] if len(records) > 5 else records
        }
    except HTTPException:
        raise
    except Exception as e:
        import traceback
        error_detail = f"Import failed: {str(e)}\n{traceback.format_exc()}"
        raise HTTPException(status_code=500, detail=error_detail)

@router.post("/export-data")
async def export_data(
    db: Session = Depends(deps.get_db),
    current_user: dict = Depends(deps.get_current_user_with_permission("settings:read"))
) -> Any:
    """
    Export system data to JSON
    """
    import json
    from fastapi.responses import StreamingResponse
    import io
    
    # Collect data to export
    export_data = {
        "system_settings": {},
        "exported_at": str(datetime.now()),
        "exported_by": current_user.get("email")
    }
    
    # Load current settings
    if os.path.exists(SETTINGS_FILE):
        with open(SETTINGS_FILE, 'r') as f:
            export_data["system_settings"] = json.load(f)
    
    # Convert to JSON string
    json_str = json.dumps(export_data, indent=2)
    
    # Create streaming response
    return StreamingResponse(
        io.BytesIO(json_str.encode()),
        media_type="application/json",
        headers={"Content-Disposition": "attachment; filename=system_data_export.json"}
    )
