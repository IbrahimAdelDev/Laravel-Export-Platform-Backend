FROM python:3.11-slim

WORKDIR /app

# منع بايثون من كتابة ملفات .pyc وتوجيه اللوجز مباشرة
ENV PYTHONDONTWRITEBYTECODE 1
ENV PYTHONUNBUFFERED 1

COPY requirements.txt /app/
RUN pip install --no-cache-dir -r requirements.txt

COPY . /app/

# تشغيل السيرفر على بورت 8000
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000"]