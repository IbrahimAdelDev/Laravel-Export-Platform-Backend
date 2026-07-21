import pandas as pd
import numpy as np
from datetime import datetime
from core.database import get_db_engine

class RecommendationService:
    def __init__(self):
        self.engine = get_db_engine()

    def get_best_countries(self, product_id: int):
        current_year = datetime.now().year
        
        # 1. جلب بيانات آخر 5 سنوات لزيادة عمق التحليل
        query = f"""
            SELECT 
                c.name_ar as country_name,
                ts.year as year,
                ts.quantity as qty,
                ts.value_million_usd as val
            FROM trade_statistics ts
            JOIN countries c ON ts.destination_country_id = c.id
            WHERE ts.product_id = {product_id}
            AND ts.year > {current_year - 5}
        """
        
        df = pd.read_sql(query, self.engine)
        
        if df.empty:
            return {"message": "بيانات غير كافية لإجراء تحليل دقيق"}

        # ----------------------------------------------------
        # الحسابات الدقيقة (Time-Decay Logic)
        # ----------------------------------------------------
        
        # حساب "عمر" البيانات (كم سنة مرت على السجل)
        df['age'] = current_year - df['year']
        
        # تطبيق معامل الاضمحلال (Decay Factor)
        # السنة الماضية (age=1) تأخذ وزن 1.0، السنة اللي قبلها (age=2) تأخذ وزن 0.7، وهكذا
        # المعادلة: weight = e^(-0.35 * age)
        df['time_weight'] = np.exp(-0.35 * (df['age'] - 1))

        # ضرب الكميات والقيم في الوزن الزمني قبل التجميع
        df['weighted_qty'] = df['qty'] * df['time_weight']
        df['weighted_val'] = df['val'] * df['time_weight']

        # 2. التجميع لكل دولة بناءً على البيانات المرجحة
        grouped = df.groupby('country_name').agg({
            'weighted_qty': 'sum',
            'weighted_val': 'sum',
            'qty': 'sum', # للذكر فقط في الرد النهائي
            'time_weight': 'mean' # متوسط ثبات الدولة في السوق
        }).reset_index()

        # 3. حساب سعر الوحدة المرجح (Weighted Unit Price)
        # هذا السعر يعطي قيمة أكبر لأسعار الصفقات الحديثة
        grouped['weighted_avg_price'] = grouped['weighted_val'] / grouped['weighted_qty'].replace(0, 1)

        # 4. النورماليزيشن الاحترافي (Scaling)
        # السعر المرجح (65% من السكور) + الكمية المرجحة (25% من السكور) + ثبات التواجد (10% من السكور)
        grouped['price_score'] = grouped['weighted_avg_price'] / grouped['weighted_avg_price'].max()
        grouped['qty_score'] = grouped['weighted_qty'] / grouped['weighted_qty'].max()
        grouped['consistency_score'] = grouped['time_weight'] # يعبر عن مدى حداثة وتكرار التعامل مع الدولة

        grouped['final_score'] = (
            (grouped['price_score'] * 0.65) + 
            (grouped['qty_score'] * 0.25) + 
            (grouped['consistency_score'] * 0.10)
        )

        # 5. الترتيب والفلترة
        best_countries = grouped.sort_values(by='final_score', ascending=False).head(5)

        result = []
        for _, row in best_countries.iterrows():
            result.append({
                "country": row['country_name'],
                "weighted_price": round(row['weighted_avg_price'], 4),
                "market_share_index": round(row['qty_score'] * 100, 1),
                "reliability_score": round(row['consistency_score'] * 100, 1),
                "recommendation_score": round(row['final_score'] * 100, 2)
            })

        return result