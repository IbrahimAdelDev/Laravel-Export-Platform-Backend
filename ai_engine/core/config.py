from pydantic_settings import BaseSettings

class Settings(BaseSettings):
    # أسماء المتغيرات هنا لازم تكون مطابقة للي في الـ docker-compose.yml 
    # بس الحروف تكون Small (Pydantic ذكي وبيربطها أوتوماتيك بالـ CAPITAL اللي في البيئة)
    AI_DB_URL: str 
    AI_SECRET_KEY: str

    class Config:
        env_file = ".env"

settings = Settings()