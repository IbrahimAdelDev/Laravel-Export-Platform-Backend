from fastapi import Security, HTTPException, status
from fastapi.security.api_key import APIKeyHeader
from core.config import settings  # استدعاء الإعدادات

API_KEY_NAME = "X-AI-Secret-Key"
api_key_header = APIKeyHeader(name=API_KEY_NAME, auto_error=True)

async def verify_api_key(api_key: str = Security(api_key_header)):
    # استخدام المتغير من الـ settings بدلاً من os.getenv
    expected_key = settings.AI_SECRET_KEY
    
    if api_key != expected_key:
        raise HTTPException(
            status_code=status.HTTP_403_FORBIDDEN, 
            detail="Could not validate credentials"
        )
    return api_key