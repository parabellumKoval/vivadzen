# Store Package Analysis & Regional Product Content Strategy

## 📦 Package Overview: `src/api/packages/store`

### Core Purpose
Backpack-store — полнофункциональный Laravel пакет для e-commerce с поддержкой:
- Мультиязычного каталога (Spatie/translatable)
- Многорегиональной торговли (country codes: uk, es, de, cz)
- Кеширования каталога (ak_catalog, ak_catalog_attr таблицы)
- Вариантов товаров (модификации)
- Поставщиков и ценовых переопределений

---

## 🏗️ Architecture

### Database Models (src/app/Models)

| Model | Purpose | Key Relations |
|-------|---------|----------------|
| `Product` | Основной товар | categories, suppliers, modifications, parent |
| `ProductCountryOverride` | Переопределения по странам | только цена/валюта |
| `SupplierProduct` | Товары от поставщика | supplier, country_code, stock |
| `Category` | Категории (с content) | products, children, parent |
| `Supplier` | Поставщики | products, countries |
| `Attribute` | Характеристики | values, products |

### Configuration (src/config)

```php
// multistore.php
'countries' => [
    'uk' => ['country' => 'Ukraine', 'locale' => 'uk', 'currency' => 'UAH'],
    'cz' => ['country' => 'Czech', 'locale' => 'cz', 'currency' => 'CZK'],
    'de' => ['country' => 'Germany', 'locale' => 'de', 'currency' => 'EUR'],
    'es' => ['country' => 'Spain', 'locale' => 'es', 'currency' => 'EUR'],
]
```

---

## 🔴 ПРОБЛЕМА: Content по регионам

### Текущая архитектура

```php
// Product Model
protected $translatable = ['name', 'short_name', 'content', 'excerpt', 'merchant_content', ...];
// ↓ Spatie/translatable
// JSON колонка в DB: 
// {
//   "uk": {"content": "Опис українською"},
//   "ru": {"content": "Описание на русском"}
// }
```

**Проблемы:**
1. **Один content для всех языков внутри региона** — нет различия между регионом и языком
   - Украина: uk, ru языки (один content на обоих)
   - Чехия: cz, en, ru языки (один content на всех)
   
2. **Нет региональных описаний** — для каждой страны свой текст, но их хранение привязано к языку, а не к стране

3. **CatalogCache не поддерживает регион-специфичный content** — в `ak_catalog` `content` копируется из `products.content` без учета региона

---

## ✅ РЕШЕНИЕ: Regional Product Content

### Архитектура новой таблицы

#### 1. Новая таблица `ak_product_regional_content`

```sql
CREATE TABLE ak_product_regional_content (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    product_id BIGINT UNSIGNED NOT NULL,
    country_code VARCHAR(2) NOT NULL,        -- 'uk', 'cz', 'de', 'es'
    content LONGTEXT,                        -- HTML описание
    excerpt VARCHAR(500),                    -- Краткое описание
    merchant_content TEXT,                   -- Для Google Merchant
    
    -- Переводы (JSON)
    translations JSON,  -- {"uk": {...}, "ru": {...}, "en": {...}}
    
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_product_country (product_id, country_code),
    FOREIGN KEY (product_id) REFERENCES ak_products(id) ON DELETE CASCADE
);

-- Формат translations JSON:
-- {
--   "uk": {
--     "content": "Опис українською",
--     "excerpt": "Короткий опис",
--     "merchant_content": "Google Merchant опис"
--   },
--   "ru": {
--     "content": "Описание на русском",
--     "excerpt": "Краткое описание",
--     "merchant_content": "Google Merchant опис"
--   },
--   "en": { ... },
--   "cz": { ... }
-- }
```

#### 2. Fallback Logic для бэкомпатибильности

```
Если regional content НЕ заполнен → использовать глобальный Product.content
Если regional content заполнен → использовать его
```

---

## 🔧 Implementation Steps

### Step 1: Model Relations (1-2 часа)

**Файл:** `src/app/Models/Product.php`

```php
<?php
namespace Backpack\Store\app\Models;

class Product extends Model
{
    // Новое отношение
    public function regionalContents()
    {
        return $this->hasMany(ProductRegionalContent::class, 'product_id');
    }

    // Метод для получения content по стране
    public function getRegionalContent(?string $countryCode = null): ?ProductRegionalContent
    {
        $country = $countryCode ?? \Store::context()->country ?? 'zz';
        return $this->regionalContents()
                   ->where('country_code', $country)
                   ->first();
    }

    // Accessor для backwards compatibility
    public function getContentAttribute()
    {
        // Если есть контекст страны и regional content — использовать его
        if ($countryCode = \Store::context()->country ?? null) {
            $regional = $this->getRegionalContent($countryCode);
            if ($regional?->content) {
                return $regional->content;
            }
        }
        // Fallback на глобальный content
        return $this->attributes['content'] ?? null;
    }
}
```

**Новый Model:** `src/app/Models/ProductRegionalContent.php`

```php
<?php
namespace Backpack\Store\app\Models;

use Illuminate\Database\Eloquent\Model;
use Backpack\CRUD\app\Models\Traits\SpatieTranslatable\HasTranslations;

class ProductRegionalContent extends Model
{
    use HasTranslations;
    
    protected $table = 'ak_product_regional_content';
    protected $fillable = ['product_id', 'country_code', 'content', 'excerpt', 'merchant_content', 'translations'];
    
    protected $translatable = ['content', 'excerpt', 'merchant_content'];
    protected $casts = ['translations' => 'array'];
    
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Получить переводы для конкретного языка
    public function getTranslatedContent(string $language): ?string
    {
        return $this->getTranslation('content', $language);
    }
}
```

---

### Step 2: Admin CRUD Interface (2-3 часа)

**Файл:** `src/app/Http/Controllers/Admin/ProductCrudController.php`

```php
protected function setupCreateOperation()
{
    // ... existing fields
    
    // Добавить tab для региональных описаний
    $this->crud->addField([
        'name' => 'regional_content_tab',
        'type' => 'custom_html',
        'value' => view('store-crud::regional-content-tab', [
            'countries' => config('dress.store.multistore.countries'),
            'entry' => $this->crud->entry ?? null
        ])->render()
    ]);
}

// Переопределить сохранение
public function store()
{
    $response = parent::store();
    
    // Сохранить региональные контенты
    $this->saveRegionalContents($this->crud->entry);
    
    return $response;
}

private function saveRegionalContents(Product $product)
{
    $regionalData = request()->input('regional_content', []);
    
    foreach ($regionalData as $countryCode => $data) {
        $product->regionalContents()->updateOrCreate(
            ['country_code' => $countryCode],
            [
                'content' => $data['content'] ?? null,
                'excerpt' => $data['excerpt'] ?? null,
                'merchant_content' => $data['merchant_content'] ?? null,
                'translations' => $data['translations'] ?? null,
            ]
        );
    }
}
```

**Blade View:** `resources/views/store-crud/regional-content-tab.blade.php`

```blade
<div class="card">
    <div class="card-header">
        <h5>Regional Content by Country</h5>
    </div>
    <div class="card-body">
        <ul class="nav nav-tabs" role="tablist">
            @foreach($countries as $code => $config)
                <li class="nav-item">
                    <a class="nav-link {{ $loop->first ? 'active' : '' }}" 
                       data-toggle="tab" 
                       href="#region-{{ $code }}">
                        {{ $config['country'] }}
                    </a>
                </li>
            @endforeach
        </ul>

        <div class="tab-content mt-3">
            @foreach($countries as $code => $config)
                @php
                    $regional = $entry?->getRegionalContent($code);
                @endphp
                <div id="region-{{ $code }}" 
                     class="tab-pane {{ $loop->first ? 'active' : '' }}">
                    
                    <!-- Content Editor (WYSIWYG) -->
                    <div class="form-group">
                        <label>Content ({{ $config['country'] }})</label>
                        <textarea name="regional_content[{{ $code }}][content]" 
                                  class="form-control editor">
                            {{ $regional?->content }}
                        </textarea>
                    </div>

                    <!-- Excerpt -->
                    <div class="form-group">
                        <label>Excerpt</label>
                        <textarea name="regional_content[{{ $code }}][excerpt]" 
                                  class="form-control" rows="2">
                            {{ $regional?->excerpt }}
                        </textarea>
                    </div>

                    <!-- Google Merchant -->
                    <div class="form-group">
                        <label>Google Merchant Content</label>
                        <textarea name="regional_content[{{ $code }}][merchant_content]" 
                                  class="form-control" rows="2">
                            {{ $regional?->merchant_content }}
                        </textarea>
                    </div>

                    <!-- Translations Switcher -->
                    <div class="form-group">
                        <label>Languages</label>
                        <div class="btn-group btn-group-toggle" role="group">
                            @foreach($config['languages'] ?? ['uk', 'ru', 'en'] as $lang)
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary language-switcher"
                                        data-lang="{{ $lang }}"
                                        data-country="{{ $code }}">
                                    {{ strtoupper($lang) }}
                                </button>
                            @endforeach
                        </div>
                        
                        <!-- Language-specific content -->
                        @foreach($config['languages'] ?? ['uk', 'ru', 'en'] as $lang)
                            <div class="lang-content d-none" 
                                 data-lang="{{ $lang }}" 
                                 data-country="{{ $code }}">
                                <textarea name="regional_content[{{ $code }}][translations][{{ $lang }}][content]" 
                                          class="form-control editor mt-2"
                                          placeholder="Content in {{ $lang }}">
                                    {{ $regional?->getTranslation('content', $lang) }}
                                </textarea>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
```

---

### Step 3: Cache Sync Updates (2 часа)

**Файл:** `src/app/Services/Catalog/CatalogCacheService.php`

```php
public function buildCatalogRow(Product $p, string $countryCode): array
{
    // ... existing code ...
    
    // Получить региональный content (или fallback на глобальный)
    $regionalContent = $p->getRegionalContent($countryCode);
    
    return [
        'product_id' => $p->id,
        'country_code' => $countryCode,
        'content' => $regionalContent?->content ?? $p->content,
        'merchant_content' => $regionalContent?->merchant_content ?? $p->merchant_content,
        'excerpt' => $regionalContent?->excerpt ?? $p->excerpt,
        // ... rest of fields
    ];
}
```

---

### Step 4: API Resources Update (1-2 часа)

**Файл:** `src/app/Http/Resources/ProductLargeResource.php`

```php
public function toArray($request)
{
    // Получить контекст
    $countryCode = \Store::context()->country ?? null;
    
    return [
        // ... existing fields
        'content' => $this->getContentForRegion($countryCode),
        'excerpt' => $this->getExcerptForRegion($countryCode),
        'merchant_content' => $this->getMerchantContentForRegion($countryCode),
    ];
}

private function getContentForRegion(?string $countryCode): ?string
{
    if (!$countryCode) {
        return $this->content;
    }
    
    $regional = $this->regionalContents()
                     ->where('country_code', $countryCode)
                     ->first();
                     
    return $regional?->content ?? $this->content;
}
```

---

### Step 5: Frontend Implementation (2-3 часа)

**Файл:** `src/front/components/Product/Content.vue`

```vue
<template>
  <div class="product-content">
    <div v-if="regionalContent" v-html="regionalContent.content"></div>
    <div v-else v-html="product.content"></div>
  </div>
</template>

<script setup>
const { product, region } = defineProps(['product', 'region']);

const regionalContent = computed(() => {
    return product.regional_contents?.find(rc => rc.country_code === region);
});
</script>
```

---

## 📋 Migration Path

### Migration File (создать)

```php
<?php
// database/migrations/YYYY_MM_DD_create_product_regional_content_table.php

return new class extends Migration {
    public function up()
    {
        Schema::create('ak_product_regional_content', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                  ->constrained('ak_products')
                  ->onDelete('cascade');
            $table->string('country_code', 2);
            $table->longText('content')->nullable();
            $table->string('excerpt', 500)->nullable();
            $table->text('merchant_content')->nullable();
            $table->json('translations')->nullable();
            $table->timestamps();
            
            $table->unique(['product_id', 'country_code']);
            $table->index('country_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('ak_product_regional_content');
    }
};
```

---

## 🔄 Workflow в админ-панели

### Редактирование товара

```
Product Edit Form
├── Basic Info (Name, SKU, etc.)
├── Pricing
├── Categories
├── [NEW] Regional Content ← новый tab
│   ├── Ukraine
│   │   ├── Content (WYSIWYG)
│   │   ├── Excerpt
│   │   ├── Google Merchant
│   │   └── Language Switcher [UK] [RU]
│   ├── Czech
│   │   ├── Content
│   │   ├── Excerpt
│   │   ├── Google Merchant
│   │   └── Language Switcher [CZ] [RU] [EN]
│   ├── Germany
│   │   └── ... similar
│   └── Spain
│       └── ... similar
└── Save Product
```

---

## 📊 Data Flow Examples

### Пример 1: Украина (uk) — русский язык

```
API Request: GET /api/products/123?country=uk&locale=ru

ProductLargeResource::toArray()
  ├─ Get regional content for 'uk'
  │  └─ ProductRegionalContent::where('product_id', 123)->where('country_code', 'uk')->first()
  ├─ Get translation for 'ru'
  │  └─ regional->getTranslation('content', 'ru')
  └─ Return: {"content": "Описание на русском для Украины", ...}
```

### Пример 2: Чехия (cz) — чешский язык

```
API Request: GET /api/products/456?country=cz&locale=cz

CatalogCache::buildCatalogRow()
  ├─ Get regional content for 'cz'
  │  └─ regional->getTranslation('content', 'cz')
  └─ Save to ak_catalog: {"content": "Popis pro Českou republiku", ...}
```

---

## 🎯 Затронутые файлы

### Core Package Changes
| File | Change | Type |
|------|--------|------|
| `src/app/Models/Product.php` | +relation, +accessor | Model |
| `src/app/Models/ProductRegionalContent.php` | NEW | Model |
| `src/app/Http/Controllers/Admin/ProductCrudController.php` | +save logic | Controller |
| `src/app/Services/Catalog/CatalogCacheService.php` | +regional logic | Service |
| `src/app/Http/Resources/ProductLargeResource.php` | +region methods | Resource |
| `database/migrations/YYYY_...create_regional_content.php` | NEW | Migration |
| `resources/views/store-crud/regional-content-tab.blade.php` | NEW | View |

### App-level Changes
| File | Change | Type |
|------|--------|------|
| `src/api/resources/views/admin/products/edit.blade.php` | +regional tab | Overrides |
| `src/front/composables/useProductContent.ts` | +region logic | Frontend |
| `src/api/app/Http/Resources/ProductLargeResource.php` | extend | Override |

---

## ⚠️ Backwards Compatibility

### Migration Strategy

1. **Phase 1**: Добавить таблицу (миграция)
2. **Phase 2**: Заполнить regional_content из existing products.content (seeder)
3. **Phase 3**: Обновить Admin CRUD с UI для региональных контентов
4. **Phase 4**: Постепенно заполнять разные описания для разных стран
5. **Phase 5**: Обновить API и frontend

### Fallback система

```php
// Всегда work существующий код:
$product->content  // ← автоматически вернёт regional или fallback на глобальный

// Новый способ:
$product->getRegionalContent('cz')->content  // ← специфичный контент для Чехии
```

---

## 🚀 Integration Checklist

- [ ] Create migration for `ak_product_regional_content`
- [ ] Create `ProductRegionalContent` model
- [ ] Add relations to `Product` model
- [ ] Create/update admin CRUD fields
- [ ] Update `CatalogCacheService` (sync logic)
- [ ] Update API resources (ProductLargeResource)
- [ ] Update frontend components
- [ ] Create data seeder (migrate existing content)
- [ ] Add tests
- [ ] Update documentation
- [ ] Deploy & test fallback behavior

---

## 💡 Future Enhancements

1. **AI Content Generation**: Per-country content generation via DeepL
2. **Content Versioning**: Track content changes per region/language
3. **A/B Testing**: Test different content per region
4. **SEO Optimization**: Region-specific meta tags & structured data
5. **Translation Workflow**: Built-in translation status tracking
6. **Bulk Operations**: Mass update regional content from CSV

---

## 📝 Summary

**Проблема**: Один content для всех регионов и языков

**Решение**: Новая таблица `ak_product_regional_content` с:
- Привязкой к country_code (uk, cz, de, es)
- JSON translations для каждого языка в стране
- Fallback на глобальный content если региональный не заполнен
- Интеграцией с CatalogCache для оптимизации

**Преимущества**:
✅ Полная региональная кастомизация
✅ Backwards compatible
✅ Гибкая система перевода (язык != регион)
✅ Оптимизирована для кеша
✅ Admin-friendly interface
