# 03 — Компоненты

## Компонент: TgLayout

**Файл:** `components/layout/TgLayout.vue`

Обёртка для всех страниц.

```vue
<template>
  <div class="tg-layout">
    <TgTopBar v-bind="topBarProps" />
    <main class="tg-main">
      <slot />
    </main>
    <TgBottomNav />
  </div>
</template>
```

```css
.tg-layout {
  min-height: 100dvh;
  background: var(--color-bg);
  max-width: 480px;
  margin: 0 auto;
}
.tg-main {
  padding-top: 56px;          /* высота TopBar */
  padding-bottom: 72px;       /* высота BottomNav + запас */
}
```

---

## Компонент: TgTopBar

**Файл:** `components/layout/TgTopBar.vue`

Props:
```ts
{
  title?: string
  showBack?: boolean      // показать кнопку назад
  showLang?: boolean      // показать переключатель языка
  transparent?: boolean   // прозрачный фон (для страницы товара)
}
```

Структура:
```
[ ← ]    [ Заголовок ]    [ 🌍 ]
```

```css
.tg-topbar {
  position: fixed;
  top: 0; left: 0; right: 0;
  height: 56px;
  background: var(--color-white);
  border-bottom: 1px solid var(--color-border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 8px;
  z-index: 100;
  max-width: 480px;
  margin: 0 auto;
}
```

Кнопка «Назад»: вызывает `router.back()` или `navigateTo` к каталогу если история пустая.

---

## Компонент: TgBottomNav

**Файл:** `components/layout/TgBottomNav.vue`

```
┌─────────────────────────────────────────┐
│  [🏠 Каталог]  [🛒 2]  [📋 Заказы]  [👤] │
└─────────────────────────────────────────┘
```

```css
.tg-bottom-nav {
  position: fixed;
  bottom: 0; left: 0; right: 0;
  max-width: 480px;
  margin: 0 auto;
  height: 56px;
  padding-bottom: env(safe-area-inset-bottom);
  background: var(--color-white);
  border-top: 1px solid var(--color-border);
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  z-index: 100;
}
```

Каждый элемент — иконка + текст подписи, выровнены по центру.  
Активный: `color: var(--color-primary)`.  
Бейдж корзины: круглый, `background: var(--color-accent)`, абсолютно позиционирован над иконкой.

---

## Компонент: Карточка товара (TgProductCard)

**Файл:** `components/catalog/TgProductCard.vue`

Props:
```ts
{
  product: Product   // тип из src/front
}
```

### Визуальная структура
```
┌────────────────────┐
│  [SALE]            │  ← бейдж если есть скидка
│                    │
│    [Фото товара]   │  ← aspect-ratio: 1/1, contain
│                    │
├────────────────────┤
│  Название товара   │  ← 2 строки max, ellipsis
│  и ещё текст...    │
│                    │
│  ~~22300~~  300 грн│  ← зачёркнутая + актуальная
│                    │
│  [+ Добавить]      │  ← кнопка или счётчик
└────────────────────┘
```

```css
.product-card {
  background: var(--color-white);
  border-radius: var(--radius-lg);
  overflow: hidden;
  box-shadow: var(--shadow-card);
  display: flex;
  flex-direction: column;
}
.product-card__image-wrap {
  position: relative;
  background: var(--color-bg-card);
  aspect-ratio: 1/1;
}
.product-card__image {
  width: 100%; height: 100%;
  object-fit: contain;
  padding: 8px;
}
.product-card__body {
  padding: 10px 10px 12px;
  display: flex;
  flex-direction: column;
  flex: 1;
}
.product-card__name {
  font-size: 13px;
  font-weight: 500;
  line-height: 1.4;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
  flex: 1;
}
.product-card__price-row {
  margin-top: 6px;
  display: flex;
  align-items: baseline;
  gap: 6px;
}
.product-card__price-old {
  font-size: 11px;
  color: var(--color-text-muted);
  text-decoration: line-through;
}
.product-card__price {
  font-size: 15px;
  font-weight: 700;
  color: var(--color-accent);
}
```

### Кнопка «Добавить»

Если товар **без модификаций**:
- Кнопка «+ Добавить» → сразу добавляет в корзину
- После добавления — трансформируется в счётчик `− 1 +`

Если товар **с модификациями**:
- Кнопка «+ Добавить» → открывает **Bottom Sheet выбора модификаций**

```css
.product-card__btn {
  margin-top: 10px;
  width: 100%;
  padding: 9px 0;
  border-radius: var(--radius-sm);
  background: var(--color-primary);
  color: white;
  font-size: 13px;
  font-weight: 600;
  border: none;
  cursor: pointer;
}
.product-card__counter {
  display: flex;
  align-items: center;
  justify-content: space-between;
  background: var(--color-bg-card);
  border-radius: var(--radius-sm);
  padding: 4px 8px;
  margin-top: 10px;
}
.product-card__counter button {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: var(--color-primary);
  color: white;
  border: none;
  font-size: 18px;
  line-height: 1;
}
```

---

## Компонент: Скелетон карточки (TgProductCardSkeleton)

**Файл:** `components/catalog/TgProductCardSkeleton.vue`

Повторяет структуру карточки, все блоки — `div.skeleton`.

```html
<div class="product-card">
  <div class="skeleton" style="aspect-ratio:1/1"></div>
  <div style="padding:10px">
    <div class="skeleton" style="height:14px;border-radius:4px;margin-bottom:6px"></div>
    <div class="skeleton" style="height:14px;width:60%;border-radius:4px;margin-bottom:10px"></div>
    <div class="skeleton" style="height:18px;width:40%;border-radius:4px;margin-bottom:10px"></div>
    <div class="skeleton" style="height:34px;border-radius:8px"></div>
  </div>
</div>
```

---

## Компонент: Переключатель модификаций

**Файл:** `components/product/TgVariantPicker.vue`

Используется на странице товара.

Визуально — горизонтальный список чипов (или вертикальные карточки если вариантов много):

```
Выберите объём:
[ 3.5g ]  [ 7g ]  [ 14g ✓ ]  [ 28g ]
```

Активный вариант — зелёная рамка + зелёный фон.  
Если у варианта другая цена — отображать её под чипом или внутри.

```css
.variant-chip {
  padding: 8px 16px;
  border-radius: var(--radius-md);
  border: 1.5px solid var(--color-border);
  background: var(--color-white);
  font-size: 14px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.15s;
}
.variant-chip.active {
  border-color: var(--color-primary);
  background: rgba(115, 197, 111, 0.1);
  color: var(--color-primary);
  font-weight: 600;
}
```

---

## Компонент: Bottom Sheet выбора модификации

**Файл:** `components/product/TgVariantSheet.vue`

Открывается при нажатии «+ Добавить» на карточке товара с вариантами.

Структура:
```
────────────                    ← drag handle
Выберите вариант

[Фото]  Название товара
        ~~Старая цена~~  Актуальная цена

Объём:
[ 3.5g ]  [ 7g ]  [ 14g ]

        [Добавить в корзину]     ← Accent кнопка
```

После выбора варианта и нажатия «Добавить» — sheet закрывается, товар в корзине.  
Если вариант не выбран — кнопка disabled.

---

## Компонент: Полоска категорий (TgCategoryBar)

**Файл:** `components/catalog/TgCategoryBar.vue`

```html
<div class="category-bar">
  <div class="category-bar__pill" 
       v-for="cat in categories"
       :class="{ active: cat.slug === activeCategory }"
       @click="navigate(cat)">
    {{ cat.name }}
    <span class="category-bar__count">{{ cat.count }}</span>
  </div>
</div>
```

```css
.category-bar {
  display: flex;
  gap: 8px;
  overflow-x: auto;
  scrollbar-width: none;
  padding: 12px 16px;
  position: sticky;
  top: 56px;   /* под TopBar */
  background: var(--color-bg);
  z-index: 50;
}
.category-bar::-webkit-scrollbar { display: none; }
.category-bar__pill {
  flex-shrink: 0;
  padding: 7px 14px;
  border-radius: var(--radius-full);
  border: 1.5px solid var(--color-border);
  background: var(--color-white);
  font-size: 13px;
  font-weight: 600;
  color: var(--color-text-muted);
  cursor: pointer;
  white-space: nowrap;
  display: flex;
  align-items: center;
  gap: 5px;
}
.category-bar__pill.active {
  background: var(--color-primary);
  border-color: var(--color-primary);
  color: white;
}
.category-bar__count {
  font-size: 11px;
  font-weight: 400;
  opacity: 0.8;
}
```

---

## Компонент: Bottom Sheet (базовый)

**Файл:** `components/ui/TgBottomSheet.vue`

Переиспользуемая обёртка для всех bottom sheet в приложении.

Props:
```ts
{
  modelValue: boolean   // v-model для open/close
  title?: string
}
```

```vue
<template>
  <Teleport to="body">
    <Transition name="sheet">
      <div v-if="modelValue" class="sheet-overlay" @click.self="$emit('update:modelValue', false)">
        <div class="sheet">
          <div class="sheet__handle"></div>
          <div class="sheet__title" v-if="title">{{ title }}</div>
          <slot />
        </div>
      </div>
    </Transition>
  </Teleport>
</template>
```

```css
.sheet-overlay {
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.4);
  z-index: 200;
  display: flex;
  align-items: flex-end;
}
.sheet {
  width: 100%;
  max-width: 480px;
  margin: 0 auto;
  background: var(--color-white);
  border-radius: 20px 20px 0 0;
  padding: 16px 16px calc(20px + env(safe-area-inset-bottom));
  max-height: 85vh;
  overflow-y: auto;
}
.sheet__handle {
  width: 36px; height: 4px;
  background: var(--color-border);
  border-radius: 2px;
  margin: 0 auto 16px;
}
/* Transitions */
.sheet-enter-active, .sheet-leave-active { transition: opacity 0.2s; }
.sheet-enter-active .sheet, .sheet-leave-active .sheet {
  transition: transform 0.25s cubic-bezier(0.32, 0.72, 0, 1);
}
.sheet-enter-from, .sheet-leave-to { opacity: 0; }
.sheet-enter-from .sheet, .sheet-leave-to .sheet { transform: translateY(100%); }
```

---

## Компонент: Счётчик в корзине (TgQtyCounter)

**Файл:** `components/cart/TgQtyCounter.vue`

Props: `{ modelValue: number, min?: number (default: 1) }`

```
[ − ]   3   [ + ]
```

При уменьшении до 0 — товар удаляется из корзины (или кнопка «−» становится корзиной 🗑).

---

## Компонент: Переключатель языка (TgLangSwitcher)

**Файл:** `components/ui/TgLangSwitcher.vue`

Открывает `TgBottomSheet` со списком доступных языков для текущего региона.  
При выборе — роутер переключает `/[region]/[locale]/...` → `/[region]/[newLocale]/...`.
