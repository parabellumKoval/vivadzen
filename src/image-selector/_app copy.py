from fastapi import FastAPI, HTTPException
from pydantic import BaseModel
from PIL import Image
import imagehash
import io
import torch
import numpy as np
import requests
from typing import List, Union
import clip
import logging
import pytesseract

# Настройка логирования
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

app = FastAPI()

# Константы строгости
PRODUCT_PROB_THRESHOLD = 0.01          # Минимальная вероятность соответствия названию
BANNER_PROB_THRESHOLD = 0.7            # Максимальная вероятность самодельного баннера
HASH_DUPLICATE_THRESHOLD = 3           # Порог схожести хэшей для исключения дублей
FIRST_IMAGE_REFERENCE_PROB = 90        # Вероятность (0-100), что первый товар — образец
REFERENCE_SIMILARITY_THRESHOLD = 0.85  # Порог схожести с первым изображением
WATERMARK_KEYWORDS = {"shutterstock", "getty"}  # Явные водяные знаки
PROMO_TEXT_KEYWORDS = {"price", "discount", "sale", "buy now", "off", "promo"}  # Лишний текст

# Загружаем CLIP
device = "cuda" if torch.cuda.is_available() else "cpu"
clip_model, clip_preprocess = clip.load("ViT-B/32", device=device)

def download_image(url: str) -> Union[Image.Image, None]:
    """Загрузка изображения по URL, возвращает None при ошибке."""
    headers = {
        "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36"
    }
    try:
        response = requests.get(url, headers=headers, timeout=20)
        response.raise_for_status()
        return Image.open(io.BytesIO(response.content)).convert("RGB")
    except requests.exceptions.RequestException as e:
        logger.info(f"Failed to download image from {url}: {str(e)}")
        return None

def preprocess_image(image: Image.Image) -> torch.Tensor:
    """Преобразование изображения для CLIP."""
    return clip_preprocess(image).unsqueeze(0).to(device)

def get_image_hash(image: Image.Image) -> imagehash.ImageHash:
    """Получение перцептивного хэша изображения."""
    return imagehash.average_hash(image)

def extract_text(image: Image.Image) -> str:
    """Извлечение текста для проверки баннеров и водяных знаков."""
    try:
        return pytesseract.image_to_string(image).lower().strip()
    except Exception:
        return ""

def has_watermark(image: Image.Image) -> bool:
    """Проверка на наличие водяных знаков сайтов."""
    image_text = extract_text(image)
    has_wm = any(keyword in image_text for keyword in WATERMARK_KEYWORDS)
    if has_wm:
        logger.info(f"Image excluded due to watermark: {image_text}")
    return has_wm

def is_promo_banner(image: Image.Image) -> bool:
    """Проверка на самодельный баннер с лишним текстом."""
    image_text = extract_text(image)
    has_promo = any(keyword in image_text for keyword in PROMO_TEXT_KEYWORDS)
    if has_promo:
        logger.info(f"Image excluded as promo banner: {image_text}")
    return has_promo

def get_image_features(image: Image.Image) -> np.ndarray:
    """Извлечение признаков изображения с помощью CLIP."""
    image_input = preprocess_image(image)
    with torch.no_grad():
        features = clip_model.encode_image(image_input).cpu().numpy()[0]
    return features

def classify_image(image: Image.Image, product_name: str, reference_features: np.ndarray = None) -> dict:
    """Классификация изображения с учетом первого товара как образца."""
    image_input = preprocess_image(image)
    all_prompts = [
        "front view of a product on a white background",
        "back view of a product on a white background",
        "multiple units of a product in one photo",
        "official promotional image of a product",
        "banner with promotional text like price or discount",
        f"{product_name} on a product label"
    ]
    all_tokens = clip.tokenize(all_prompts).to(device)
    
    with torch.no_grad():
        image_features = clip_model.encode_image(image_input)
        text_features = clip_model.encode_text(all_tokens)
        logits_per_image = (image_features @ text_features.T).softmax(dim=-1)
        probs = logits_per_image.cpu().numpy()[0]
    
    banner_prob = probs[4]
    product_prob = probs[5]
    view_probs = probs[:4]
    
    if banner_prob > BANNER_PROB_THRESHOLD:
        logger.info(f"Image excluded as banner/promo: {probs}")
        return {"is_valid": False, "view": None}
    
    # Сравнение с первым изображением, если задано
    similarity = 0.0  # По умолчанию float
    if reference_features is not None:
        ref_tensor = torch.tensor(reference_features, dtype=torch.float32).to(device)
        similarity = float(torch.cosine_similarity(image_features, ref_tensor.unsqueeze(0)).cpu().numpy()[0])  # Преобразуем в float
    is_reference_match = similarity > REFERENCE_SIMILARITY_THRESHOLD
    
    best_view_idx = np.argmax(view_probs)
    view = all_prompts[best_view_idx]
    if "front" in view:
        view = "front"
    elif "back" in view:
        view = "back"
    elif "multiple" in view:
        view = "multiple_units"
    elif "official" in view:
        view = "official_promo"
    
    is_valid = product_prob > PRODUCT_PROB_THRESHOLD or (is_reference_match and FIRST_IMAGE_REFERENCE_PROB > 50)
    if not is_valid:
        logger.info(f"Image excluded: product_prob={product_prob}, similarity={similarity}")
    
    return {"is_valid": is_valid, "view": view, "product_prob": float(product_prob), "similarity": similarity}  # Все float

class ImageRequest(BaseModel):
    urls: List[str]
    product_name: str

@app.post("/select-product-images/", response_model=dict)
async def select_product_images(data: ImageRequest):
    urls = data.urls
    product_name = data.product_name
    if not urls:
        raise HTTPException(status_code=400, detail="No URLs provided")
    if not product_name:
        raise HTTPException(status_code=400, detail="No product name provided")

    # 1. Загрузка изображений
    images = []
    valid_urls = []
    for url in urls:
        img = download_image(url)
        if img is not None:
            images.append(img)
            valid_urls.append(url)

    if not images:
        raise HTTPException(status_code=400, detail="No valid images could be downloaded")

    # 2. Установка первого изображения как образца
    reference_features = None
    if images and FIRST_IMAGE_REFERENCE_PROB > 0:
        reference_features = get_image_features(images[0])
        logger.info(f"Using first image as reference with probability {FIRST_IMAGE_REFERENCE_PROB}%")

    # 3. Фильтрация по водяным знакам, баннерам и CLIP
    filtered_images = []
    filtered_urls = []
    classifications = []
    for img, url in zip(images, valid_urls):
        if has_watermark(img) or is_promo_banner(img):
            continue
        classification = classify_image(img, product_name, reference_features)
        if classification["is_valid"]:
            filtered_images.append(img)
            filtered_urls.append(url)
            classifications.append(classification)

    if not filtered_images:
        raise HTTPException(status_code=400, detail="No valid images found after filtering")

    # 4. Исключение дублей
    hashes = [get_image_hash(img) for img in filtered_images]
    unique_images = []
    unique_hashes = []
    unique_urls = []
    unique_classifications = []
    for i, (img, h, url, cls) in enumerate(zip(filtered_images, hashes, filtered_urls, classifications)):
        if all(h - uh > HASH_DUPLICATE_THRESHOLD for uh in unique_hashes):
            unique_images.append(img)
            unique_hashes.append(h)
            unique_urls.append(url)
            unique_classifications.append(cls)

    if not unique_images:
        raise HTTPException(status_code=400, detail="All images are duplicates")

    # 5. Выбор до 5 изображений с разными ракурсами
    selected_indices = []
    selected_views = set()
    for i, cls in enumerate(unique_classifications):
        view = cls["view"]
        if view not in selected_views or len(selected_views) < 4:
            selected_indices.append(i)
            selected_views.add(view)
        if len(selected_indices) >= 5:
            break

    # 6. Формируем результат
    selected_urls = [unique_urls[idx] for idx in selected_indices]
    selected_views_list = [unique_classifications[idx]["view"] for idx in selected_indices]
    selected_similarities = [float(unique_classifications[idx]["similarity"]) for idx in selected_indices]  # Преобразуем в float
    result = {
        "selected_images_count": len(selected_indices),
        "selected_urls": [
            {"url": url, "view": view, "similarity_to_reference": sim}
            for url, view, sim in zip(selected_urls, selected_views_list, selected_similarities)
        ],
        "total_unique_images": len(unique_images),
        "processed_urls": len(valid_urls),
        "total_urls": len(urls),
        "filtered_images_count": len(filtered_images)
    }
    return result

if __name__ == "__main__":
    import uvicorn
    uvicorn.run(app, host="0.0.0.0", port=8000)