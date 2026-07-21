from fastapi import FastAPI
from api.recommendation import router as recommendation_router

app = FastAPI(title="NabtaX AI Engine")

# تسجيل الراوتس
app.include_router(recommendation_router)

@app.get("/")
def health_check():
    return {"status": "AI Engine is running perfectly!"}
