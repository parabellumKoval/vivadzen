# toDo — new-kratom: контент, который сейчас захардкожен

Сейчас часть данных, которые по сути динамические (меняются от товара
к товару), вшита в Blade-шаблоны. Эти блоки оставлены как есть, чтобы
ничего не удалять — задача переехать на БД и админку.

## Блоки, требующие переезда в БД / админку

### Отзывы на странице товара
- Файл: `resources/views/components/product/reviews.blade.php`
- Сейчас: массив `$reviews` захардкожен прямо в шаблоне (3 отзыва).
- Что нужно: модель `ProductReview` (product_id, author, rating, body,
  verified, package_size, created_at), CRUD в админке, выборка по
  `product.slug` из БД, плюс агрегация (rating breakdown).

### Распределение оценок (5★/4★/3★/2★/1★)
- Файл: `resources/views/components/product/reviews.blade.php` (строка ~52)
- Сейчас: проценты захардкожены `[['5★', 89], ['4★', 8], ...]`.
- Что нужно: считать из таблицы `product_reviews` по slug.

### Q&A на странице товара
- Файл: `resources/views/components/product/qa.blade.php`
- Сейчас: массив `$questions` захардкожен (2 вопроса), `helpful` тоже.
- Что нужно: модель `ProductQuestion` + `ProductAnswer`, форма отправки
  вопроса, CRUD ответов в админке.

### Поле `origin`, `grind`, `testedAt`, `purity`, `h7mg`, `batch`
- Уже хранятся в БД (см. `products` таблица + миграции), наполняются
  через `CatalogSeeder`. Если нужно редактировать — есть админ-API
  `Admin\ProductController` (PUT /admin-api/products/{id}). UI админки
  для этих полей пока минимальный — можно расширить форму.

### Похожие товары (related)
- Уже динамические: `Catalog::related($slug)` сортирует по совпадению
  strain/color/form. Ничего делать не нужно.

### Подписка / Newsletter формы
- Файл: `resources/views/components/sections/newsletter.blade.php`,
  `resources/views/components/catalog/coming-soon.blade.php`
- Отправляют форму, но бэкенд-эндпоинт для приёма email-ов ещё не
  реализован — нужна модель `Subscriber` + контроллер.

## Контент, который статичен по своей природе (оставляем как есть)

Эти блоки заполняются один раз и не зависят от товара — менять их через
админку не имеет смысла, информация живёт в Blade-шаблонах:

- `resources/views/components/product/navod.blade.php` — общий шаг
  дозировки (одинаков для всего кратома)
- `resources/views/components/product/safety.blade.php` — общая
  информация о безопасности
- `resources/views/components/product/delivery.blade.php` — единая
  информация о доставке
- `resources/views/pages/catalog/index.blade.php` — SEO-текст внизу
  каталога + FAQ
- `resources/views/pages/static/*` — все статические страницы
  (about, terms, privacy, lab, …)
- Hero-секции главной, "Why Vivadzen", "Trusted bar" — лендинг-контент

## Прочее

- Заказы пишутся в БД асинхронно через `CreateOrderJob` в очереди `orders`.
  В dev нужно запускать воркер: `php artisan queue:work --queue=orders`.
- Промокоды (`Cart::PROMO_CODES`) сейчас захардкожены массивом в
  `App\Support\Cart`. Если их нужно редактировать через админку —
  модель `PromoCode` + CRUD.
- Сортировка в каталоге (`<select>` в product-grid bar) пока не имеет
  обработчика — это next-step для Alpine-фильтра.
