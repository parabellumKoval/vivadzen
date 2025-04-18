FROM python:3.9-slim

WORKDIR /app

# Установка Tesseract и зависимостей
RUN apt-get update && apt-get install -y \
    tesseract-ocr libtesseract-dev git \
    && rm -rf /var/lib/apt/lists/*

# Установка зависимостей
COPY src/image-selector/requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

# COPY src/image-selector/app.py .

CMD ["uvicorn", "app:app", "--host", "0.0.0.0", "--port", "8000", "--reload"]