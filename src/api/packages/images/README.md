# parabellumkoval/backpack-images

Пакет предоставляет единый сервис загрузки изображений и вспомогательные инструменты для Laravel Backpack 4.1. Он поддерживает несколько провайдеров хранения (локальный диск, BunnyCDN и любые кастомные) и позволяет легко подключать repeatable-поля и колонки для CRUD.

## Установка

1. Подключите пакет через `composer.json` проекта (в репозитории уже добавлен путь `packages/*`).
2. Зарегистрируйте сервис-провайдер, если не используете автоматическое обнаружение:

```php
// bootstrap/providers.php
return [
    // ...
    ParabellumKoval\BackpackImages\BackpackImagesServiceProvider::class,
];
```

3. Опубликуйте конфигурацию (опционально):

```bash
php artisan vendor:publish --tag=backpack-images-config
```

## Конфигурация

Файл `config/backpack-images.php` содержит:

- `default_provider` — имя провайдера по умолчанию (`local`, `bunny` и т.д.);
- `default_folder` — базовая папка для хранения изображений;
- `preserve_original_name` и `generate_unique_name` — стратегия именования файлов;
- `providers` — список провайдеров. Каждый провайдер указывает класс драйвера и необходимые параметры.

Пример для BunnyCDN и локального диска уже настроен. Вы можете добавить свои провайдеры, реализовав интерфейс `ParabellumKoval\BackpackImages\Contracts\ImageStorageProvider` или расширив `ConfigurableImageProvider`.

## Использование в моделях

Подключите трейт `HasImages` к Eloquent-модели:

```php
use Illuminate\Database\Eloquent\Model;
use ParabellumKoval\BackpackImages\Traits\HasImages;

class Article extends Model
{
    use HasImages;

    // Опционально переопределите настройки
    public static function imageProviderName(): string
    {
        return 'bunny';
    }

    public static function imageStorageFolder(): string
    {
        return 'articles';
    }

    public static function imageFieldPrefix(): string
    {
        return config('services.cdn.articles_url');
    }
}
```

Трейт автоматически приводит поле `images` к массиву и предоставляет набор вспомогательных методов:

- `getAllImages()` — массив всех элементов (alt/title/size/src);
- `getFirstImage()` и `getFirstImageUrl()` — первая картинка и её URL;
- `getImagesLimited($limit)` — несколько первых изображений;
- `getImagePaths()` / `getImageUrls()` — пути или полные ссылки;
- `getImageSourcesForApi($limit = null)` — массив, готовый для API.

Загрузка из URL:

```php
$stored = $article->uploadImageFromUrl('https://example.com/image.jpg');
$article->images = array_merge($article->getAllImages(), [[
    'src' => $stored->path,
    'alt' => '...',
    'title' => '...',
    'size' => 'cover',
]]);
$article->save();
```

### Несколько коллекций изображений

Одной модели можно назначить несколько независимых "коллекций" изображений. Для этого переопределите метод `imageCollections()` и добавьте дополнительные ключи — каждый ключ соответствует отдельному атрибуту и наследует дефолтные настройки:

```php
use ParabellumKoval\BackpackImages\Traits\HasImages;

class Review extends Model
{
    use HasImages;

    public static function imageCollections(): array
    {
        return array_replace_recursive(
            static::defaultImageCollections(),
            [
                'video_poster' => [
                    'folder' => 'reviews/video-posters',
                    'label' => 'Постер видео',
                    'field' => [
                        'label' => 'Постер видео',
                        'tab' => null,
                        'max_rows' => 1,
                        'fields' => [
                            [
                                'label' => 'Постер',
                                'aspect_ratio' => 16 / 9,
                            ],
                        ],
                    ],
                    'column' => [
                        'label' => 'Постер видео',
                    ],
                ],
            ]
        );
    }
}
```

После этого в CRUD можно добавить поле/колонку с именем коллекции:

```php
$this->addImagesField(['name' => 'video_poster']);
$this->addImagesColumn(['name' => 'video_poster']);
```

Все вспомогательные методы (`getAllImages`, `getFirstImage`, `getImagePaths` и др.) принимают необязательный параметр атрибута:

```php
$poster = $review->getFirstImage('video_poster');
$posterUrl = $review->getFirstImageForApi('video_poster');
```

## Компоненты для Backpack CRUD

Для контроллеров Backpack предусмотрен трейт `HasImagesCrudComponents`:

```php
use Backpack\CRUD\app\Http\Controllers\CrudController;
use ParabellumKoval\BackpackImages\Traits\HasImagesCrudComponents;

class ArticleCrudController extends CrudController
{
    use HasImagesCrudComponents;

    protected function setupCreateOperation(): void
    {
        $this->addImagesField(); // можно передать overrides
    }

    protected function setupListOperation(): void
    {
        $this->addImagesColumn(['label' => 'Preview']);
    }
}
```

Метод `addImagesField()` создаёт repeatable-поле из примера (image/alt/title/size), а `addImagesColumn()` отображает миниатюры первых изображений в списке. Оба метода принимают массив `overrides`, позволяющий переопределить любую часть конфигурации Backpack.

## Работа с провайдерами

Сервис `ImageUploader` управляет провайдерами через реестр. Вы можете получить провайдер вручную:

```php
use ParabellumKoval\BackpackImages\Services\ImageUploader;

$uploader = app(ImageUploader::class);
$provider = $uploader->getProvider('local');
$url = $provider->getUrl('articles/sample.jpg');
```

### Добавление собственного провайдера

1. Реализуйте интерфейс `ImageStorageProvider` или `ConfigurableImageProvider`.
2. Добавьте конфигурацию в `backpack-images.providers` с ключом `driver` и необходимыми параметрами.

## API фасад

Фасад `ImageUploader` остаётся доступным (совместимо со старым кодом):

```php
use ImageUploader;

$result = ImageUploader::uploadMany($urls, 'products', 'bunny');
```

Методы возвращают массивы вида `['url' => ..., 'path' => ..., 'filename' => ..., 'extension' => ...]` для обратной совместимости.

## Лицензия

MIT
