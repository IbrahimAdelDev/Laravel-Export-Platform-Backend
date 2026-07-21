from fastapi import APIRouter, Depends
from services.recommendation_service import RecommendationService
from core.security import verify_api_key

router = APIRouter()
recommendation_service = RecommendationService()

# الراوت محمي بـ verify_api_key
@router.get("/api/ai/recommend-countries/{product_id}")
async def recommend_countries(product_id: int, api_key: str = Depends(verify_api_key)):
    
    result = recommendation_service.get_best_countries(product_id)
    
    return {
        "success": True,
        "product_id": product_id,
        "recommendations": result
    }