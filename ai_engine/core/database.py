from sqlalchemy import create_engine
from core.config import settings  # استدعاء الإعدادات

# قراءة رابط الداتابيز المباشر من ملف الإعدادات
DB_URL = settings.AI_DB_URL

# لو إنت سميته في الـ config باسم مختلف زي ai_db_url، استخدمه كده:
# DB_URL = settings.ai_db_url

engine = create_engine(DB_URL)

def get_db_engine():
    return engine