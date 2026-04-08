# 01 — Дизайн-система

## Принципы UI

- Минимализм. Никакой перегруженности.
- Все элементы крупные — удобно нажимать пальцем (минимум 44px в высоту).
- Скруглённые углы везде (border-radius: 12–16px для карточек, 8–10px для кнопок и тегов).
- Тени минимальные, почти плоский дизайн.
- Фон страниц — `#fff7ed`. Карточки и инпуты — `#ffecd4`.

---

## Типографика

Использовать тот же шрифт что в `src/front`.  
Если шрифт не задан — использовать **системный sans-serif стек**:  
`font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;`

| Элемент | Размер | Вес |
|---|---|---|
| Заголовок страницы (H1) | 20px | 700 |
| Заголовок раздела (H2) | 17px | 600 |
| Название товара в карточке | 13px | 500 |
| Цена основная | 15px | 700 |
| Цена зачёркнутая | 12px | 400 |
| Кнопка | 14px | 600 |
| Лейбл / тег категории | 13px | 600 |
| Текст описания | 14px | 400 |
| Подпись / мелкий текст | 12px | 400 |

---

## Цветовые переменные

```css
:root {
  --color-primary:      #73c56f;
  --color-primary-dark: #5aad56;
  --color-accent:       #ff8400;
  --color-accent-light: #ffb347;
  --color-bg:           #fff7ed;
  --color-bg-card:      #ffecd4;
  --color-bg-input:     #ffecd4;
  --color-text:         #1a1a1a;
  --color-text-muted:   #7a7a7a;
  --color-text-light:   #aaaaaa;
  --color-border:       #e8d5b7;
  --color-white:        #ffffff;
  --color-danger:       #e53e3e;

  --radius-sm:   8px;
  --radius-md:   12px;
  --radius-lg:   16px;
  --radius-full: 999px;

  --shadow-card: 0 2px 8px rgba(0,0,0,0.06);
  --shadow-modal: 0 -4px 24px rgba(0,0,0,0.12);
}
```

---

## Сетка и отступы

- Основной контейнер: `width: 100%; max-width: 480px; margin: 0 auto;`
- Горизонтальный padding страницы: `16px`
- Каталог — 2 колонки: `grid-template-columns: 1fr 1fr; gap: 12px;`
- Вертикальный ритм между секциями: `24px`

---

## Кнопки

### Primary (основная)
```
background: var(--color-primary)
color: white
border-radius: var(--radius-md)
padding: 14px 20px
font-size: 14px; font-weight: 600
width: 100% (в большинстве случаев)
```
Состояния: hover → `var(--color-primary-dark)`, disabled → opacity 0.5

### Accent (оформить заказ, купить)
```
background: var(--color-accent)
color: white
```
Используется для финального CTA — «Оформить заказ», «Купить сейчас».

### Secondary / Outline
```
background: transparent
border: 1.5px solid var(--color-border)
color: var(--color-text)
```

### Ghost / Text
```
background: transparent
color: var(--color-primary)
no border
```
Используется для ссылок типа «Смотреть всё», «Подробнее».

---

## Теги категорий (Category Pills)

Горизонтальная полоса, скролл без скроллбара (`overflow-x: auto; scrollbar-width: none`).

```
display: inline-flex
padding: 8px 16px
border-radius: var(--radius-full)
font-size: 13px; font-weight: 600
background: var(--color-white)
border: 1.5px solid var(--color-border)
color: var(--color-text-muted)
white-space: nowrap
```

Активный тег:
```
background: var(--color-primary)
border-color: var(--color-primary)
color: white
```

---

## Бейджи и лейблы

**SALE бейдж** — красный, абсолютно позиционирован на карточке:
```
background: #e53e3e
color: white
font-size: 10px; font-weight: 700
padding: 3px 7px
border-radius: var(--radius-sm)
position: absolute; top: 8px; left: 8px
```

**Количество в категории** — серый текст рядом с названием категории.

---

## Иконки

Использовать тот же набор иконок что в `src/front`.  
Размер иконок в навигации: 24px.  
Размер в кнопках и тегах: 16px.

---

## Инпуты и поля форм

```
background: var(--color-bg-input)
border: 1.5px solid var(--color-border)
border-radius: var(--radius-md)
padding: 12px 14px
font-size: 15px
color: var(--color-text)
width: 100%
```

Focus:
```
border-color: var(--color-primary)
outline: none
```

Error:
```
border-color: var(--color-danger)
```

---

## Нижняя навигация (Bottom Tab Bar)

Фиксированная, 5 вкладок или меньше.  
Высота: 56px + safe-area-inset-bottom (Telegram).

```
background: var(--color-white)
border-top: 1px solid var(--color-border)
position: fixed; bottom: 0; left: 0; right: 0;
padding-bottom: env(safe-area-inset-bottom)
```

Иконка + подпись. Активная вкладка — цвет `var(--color-primary)`.  
Неактивная — `var(--color-text-light)`.

Вкладки:
1. 🏠 Каталог
2. 🛒 Корзина (с бейджем количества)
3. 📋 Заказы
4. 👤 Аккаунт

---

## Модальные окна / Bottom Sheets

Снизу вверх. Оверлей с `backdrop: rgba(0,0,0,0.4)`.

```
position: fixed; bottom: 0; left: 0; right: 0
background: var(--color-white)
border-radius: 20px 20px 0 0
padding: 20px 16px
padding-bottom: calc(20px + env(safe-area-inset-bottom))
box-shadow: var(--shadow-modal)
max-height: 85vh
overflow-y: auto
```

Drag-handle сверху:
```
width: 36px; height: 4px
background: var(--color-border)
border-radius: 2px
margin: 0 auto 16px
```

---

## Состояния загрузки

- Skeleton-заглушки для карточек товаров (анимация пульсации через CSS)
- Spinner на кнопке во время запроса (кнопка disabled + иконка загрузки)
- Пустые состояния (empty state) с иконкой и текстом

```css
@keyframes skeleton-pulse {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0.4; }
}
.skeleton {
  background: var(--color-bg-card);
  border-radius: var(--radius-md);
  animation: skeleton-pulse 1.4s ease-in-out infinite;
}
```
