# VIVADZEN — DESIGN TZ
## Файл 9/9 — Cheat-sheet, Definition of Done, быстрая справка

> Это короткий завершающий файл. Распечатайте его и держите рядом во время работы с Claude Design.

---

## 1. ЧТО ЗАГРУЗИТЬ В CLAUDE PROJECT KNOWLEDGE

Создайте Project «Vivadzen Design» → загрузите в knowledge:

1. **`01_DESIGN_SYSTEM.md`** (полностью) — цвета, шрифты, скейлы
2. **`03_GLOBALNI_KOMPONENTY.md`** (полностью) — Header, Footer, Mini-карточка
3. **Выжимка из `02_ASSETS_A_NANO_BANANA.md`** — только §6 (инвентарь ассетов с naming)
4. **Скриншоты Image 1, Image 2, Image 3** (ваши референсы эстетики)

**НЕ грузить в Project Knowledge:**
- Page-TZ файлы (04–08) — их добавляете в каждый отдельный чат
- Файл `00_WORKFLOW.md` — он только для вас, не для Claude
- Файл `09_DOD_CHEATSHEET.md` (этот) — он для вас

---

## 2. КАК ЗАПРОСИТЬ КАЖДУЮ СТРАНИЦУ — ШАБЛОНЫ ПРОМПТА

### Шаблон A: Базовый запрос артефакта страницы

```
Сделай артефакт страницы [НАЗВАНИЕ_СТРАНИЦЫ] Vivadzen.

Используй:
- Design System и Global Components из Project Knowledge
- Page-TZ — прикладываю файл [04/05/06/07/08]
- Референсы — 3 скриншота в Project Knowledge

Технически:
- Один HTML-файл, Tailwind через CDN, Lucide через CDN
- Google Fonts: Playfair Display (italic 700) + Inter (400/500/600/700)
- Адаптив: 360 (mobile) / 768 (tablet) / 1280 (desktop)
- Все ассеты — placeholder с alt-текстом и тегом [ASSET: ...] или [GENERATE: ...]
- Контент на чешском как в TZ
- Соблюдай комплаенс: НЕ обещай эффектов в маркетинговых секциях; обязательные warning-блоки — дословно

Output: рабочий артефакт, который я могу крутить в превью.
```

### Шаблон B: Доработка после первой версии

```
Дорабатывай артефакт. Конкретные правки:

[Секция X] — [конкретная правка: цвет, размер, layout, copy]
[Секция Y] — ...

Все остальное оставь как есть.
```

### Шаблон C: Сборка реального HTML на финал

```
Дай мне финальный артефакт со всеми правками выше. Без debug-комментариев.
Готовый к копированию в репозиторий.
```

---

## 3. DEFINITION OF DONE — ЧЕК-ЛИСТ ДЛЯ КАЖДОЙ СТРАНИЦЫ

### Universal (на всех страницах)
- [ ] Header + Footer из Global Components на месте
- [ ] Breadcrumbs корректные (если не главная)
- [ ] Title + meta description по SEO TZ
- [ ] H1 единственный, по SEO TZ
- [ ] Адаптив 360/768/1280 проверен (нет горизонтального скролла, нет clip)
- [ ] Lucide иконки в стандарте Stroke 1.5 px
- [ ] Шрифты загружены, диакритики чешские отображаются (Playfair italic + Inter)
- [ ] Все интерактивы tab-доступны, focus-ring видим
- [ ] Контраст AA для всех текстовых комбинаций
- [ ] 18+ sticky bar + age-gate подключены
- [ ] Cookie banner присутствует
- [ ] Все картинки имеют осмысленный alt или alt=""
- [ ] Footer с обязательными badges (Akreditovaná laboratoř + Autorizovaný prodejce PML)
- [ ] Footer с платёжными иконками (Visa, MC, Apple Pay, Google Pay)
- [ ] Disclaimer mandatory внизу
- [ ] LCP-элемент имеет fetchpriority="high"
- [ ] Below-the-fold картинки lazy-load

### Homepage `/`
- [ ] Hero с display-headline (lime accent word «důvěřovat»)
- [ ] 3 stats в hero
- [ ] CTA с glow-amber
- [ ] Trust strip 4 сигнала
- [ ] Categories grid 7 ячеек (как Image 3)
- [ ] Featured products 8 mini-карточек с переключателем 25/50 g
- [ ] Subscription block с мокап-карточкой
- [ ] Trusted by Thousands (Image 2 стиль) — 5 карточек с центральной выделенной
- [ ] Lab + Licence proof block
- [ ] Pruvodce teaser (3 статьи)
- [ ] Prodejny Prahy 2 точки + карта
- [ ] FAQ аккордеоны
- [ ] Schema Organization + WebSite + FAQPage

### Catalog `/kratom`
- [ ] Hero компактный с pills фильтрами
- [ ] Toolbar sticky (sort + filter)
- [ ] Sidebar filter полная (8 групп)
- [ ] Grid 4×N с MiniProductCard
- [ ] Banner «Připravujeme» 15 placeholder с email lead
- [ ] FAQ короткий

### Category `/kratom/{slug}`
- [ ] Hero уникальный для категории (типография + image)
- [ ] Sidebar с pre-checked фильтром
- [ ] Уникальный SEO-текст 800+ слов
- [ ] Schema CollectionPage + BreadcrumbList + ItemList

### Product Detail `/produkt/{slug}`
- [ ] Galery с 4-6 thumbnails
- [ ] Buy-box с цветным кружком vein + штамм + Mitragynin %
- [ ] Переключатель 25/50 g с live-update цены
- [ ] Stepper количества
- [ ] CTA Primary с glow + Secondary Předplatné
- [ ] Trust строки (доставка, безопасность, осмовівоз)
- [ ] Платёжные иконки
- [ ] 18+ badge
- [ ] Tabs sticky
- [ ] Popis produktu
- [ ] **COA таблица той самой шарже (verbатим параметров)**
- [ ] **Návod k použití 4 шага (дословно)**
- [ ] **Důležité bezpečnostní informace (дословный block)** с правильной иерархией: 18+ warning → Obecné info → Účinky → Upozornění
- [ ] Doprava a platba 2 колонки
- [ ] Recenze top-stats + photo grid + filter + list + write-form
- [ ] Otázky a odpovědi (interní) с official-badge на ответе Vivadzen
- [ ] Subscription modal с интервалом и -10%
- [ ] Related products 4
- [ ] Sticky mobile bottom bar на mobile
- [ ] Schema Product + Offer ×2 + AggregateRating + Review + BreadcrumbList + FAQPage

### Cart `/kosik`
- [ ] Список товаров с переключателем 25/50 g и stepper
- [ ] Promo-код и сертификат
- [ ] Order summary sticky
- [ ] Trust-строки
- [ ] Пустая корзина с CTA
- [ ] Schema noindex,nofollow

### Checkout `/pokladna`
- [ ] Прогресс-индикатор 3 шага
- [ ] Login/Guest gate в Step 1
- [ ] Все 4+ способа доставки (включая Express 180 min)
- [ ] Все 5+ способов оплаты с платёжными иконками
- [ ] Возможность сохранённых методов (если залогинен)
- [ ] Trust block безопасности оплаты
- [ ] Souhlasy 4 чекбокса (включая 18+ confirm)
- [ ] Финальный CTA disabled пока не отмечены required
- [ ] Loading state при processing
- [ ] noindex,nofollow

### Account `/ucet/*`
- [ ] Sidebar навигация
- [ ] Přehled dashboard
- [ ] Объednávky listing + detail (с tracking + faktury PDF + COA ZIP)
- [ ] Předplatné management (posun, pauza, zrušit)
- [ ] Adresy CRUD
- [ ] Platební metody CRUD (с токенизацией)
- [ ] Oblíbené wishlist
- [ ] Recenze a otázky с статусом модерации
- [ ] Notifikace toggle-list
- [ ] Nastavení účtu (включая Smazat účet — GDPR)

### Licence + Lab-testy + О nás + Prodejny
- [ ] Hero уникальный
- [ ] Licence-card с реквизитами (после согласования с юристом)
- [ ] COA-хаб таблица всех шarží с per-šarже страницами
- [ ] Odborný garant block (фото + bio) — критично для E-E-A-T
- [ ] LocalBusiness schema для prodejen + Google Maps embed
- [ ] Schema AboutPage / WebPage / Article

### Podpora `/podpora`
- [ ] 7 категорий тем как quick-action cards
- [ ] FAQ-аккордеоны по каждой теме
- [ ] Search-input
- [ ] Stále potřebujete pomoc форма обращения
- [ ] Chat / e-mail / phone опции

### Pruvodce + Blog + Articles
- [ ] Hub-страница `/pruvodce` со всеми 12 гайдами
- [ ] Шаблон статьи: hero + author + reading time + TOC
- [ ] Внутренние блоки: pull-quote, callouts, disclaimer
- [ ] Footer статьи: author + likes + share + related
- [ ] Schema Article + Person + Publisher

---

## 4. ОБЯЗАТЕЛЬНЫЕ КОМПЛАЕНС-БЛОКИ — ЧЕК

На product page **дословно копируем тексты:**

1. ✅ **18+ warning** (top): «Není určeno osobám mladším 18 let! Ukládat mimo dosah osob mladších 18 let!»

2. ✅ **Lead-фраза:** «Užívání tohoto výrobku může poškodit Vaše zdraví. Dbejte informací pro spotřebitele.»

3. ✅ **Obecné informace** (5 bullets — дословно)

4. ✅ **Účinky** (параграф дословно про povzbuzující/tlumivé)

5. ✅ **Upozornění** (4 bullets — дословно)

6. ✅ **Návod k použití** (4 шага — дословно)

7. ✅ **Lab test COA** — точная таблица 6 параметров с PASS chips + Stáhnout PDF

В footer:

8. ✅ **AKREDITOVANÁ LABORATOŘ** — «Testování čistoty a obsahu látek v akreditované laboratoři VŠCHT Praha dle normy ISO 17025.»

9. ✅ **AUTORIZOVANÝ PRODEJCE PML** — «Činnost pod přímým dohledem a licencí Ministerstva zdravotnictví České republiky.»

10. ✅ Disclaimer footer: «Tento výrobek není potravinou ani léčivým přípravkem. Není určeno osobám mladším 18 let.»

---

## 5. БЫСТРАЯ ПАЛИТРА (закладка)

```css
--color-forest: #1B3A2D;        /* dark surface */
--color-forest-deep: #13291F;   /* darker */
--color-forest-soft: #264E3D;   /* hover/cards on dark */
--color-cream: #F5EDD8;          /* light surface main */
--color-cream-soft: #FBF7EA;     /* lighter */
--color-cream-deep: #E8DDC1;     /* borders on light */
--color-paper: #F9F4EC;          /* lightest, alt surface */
--color-lime: #7EC855;           /* accent green */
--color-lime-deep: #5FA63B;      /* darker lime */
--color-amber: #F4A020;          /* CTA primary */
--color-amber-deep: #D88912;     /* CTA hover */
--color-amber-soft: #F8C36B;     /* focus ring, glow */
--color-terracotta: #D45C2B;     /* secondary accent */
--color-terracotta-deep: #B14A22;
--color-ink: #1A1812;            /* primary text on light */
--color-ink-soft: #4A463C;       /* subdued */
--color-mist: #8B8678;           /* placeholders */
--color-paper-on-dark: #F9F4EC;  /* text on forest */
--color-paper-soft-on-dark: #D9D4C5;
--color-border-light: #E8DDC1;
--color-border-dark: #2E5443;
--color-success: #5FA63B;
--color-warning: #F4A020;
--color-danger: #C44A2A;

/* Vein colors */
--vein-red: #B14A22;
--vein-green: #5FA63B;
--vein-white: #F9F4EC;
--vein-yellow: #E8B73A;
```

```css
/* Typography */
--font-display: 'Playfair Display', serif;
--font-body: 'Inter', sans-serif;

/* Spacing (4px baseline) */
1=4 2=8 3=12 4=16 6=24 8=32 12=48 16=64 24=96

/* Radii */
sm=4 md=8 lg=12 xl=16 2xl=24 full=9999

/* Glow */
--glow-amber: 0 0 32px rgba(244, 160, 32, 0.4);
```

---

## 6. ИКОНКИ LUCIDE (быстрая шпаргалка)

| Семантика | Lucide name |
|---|---|
| Kratom категория | `leaf` |
| Лаборатория | `flask-conical` |
| Лицензия / безопасность | `shield-check` |
| Награда / сертификат | `award` |
| Доставка | `truck` |
| Express | `zap` |
| Магазин / osmовывоз | `store` |
| Карты | `credit-card` |
| QR | `qr-code` |
| Перевод | `landmark` или `building-bank` |
| Подписка | `repeat` |
| Информация | `info` |
| FAQ | `circle-help` |
| Q&A | `message-square` |
| Звезда | `star` |
| Аккаунт | `user` |
| Корзина | `shopping-bag` |
| Поиск | `search` |
| Меню | `menu` |
| Закрыть | `x` |
| Вперёд (CTA) | `arrow-right` |
| Скачать | `download` |
| Локация | `map-pin` |
| Развернуть | `chevron-down` |
| Wishlist | `heart` |
| Удалить | `trash-2` |

---

## 7. ПОРЯДОК ЗАПУСКА (рекомендуемый)

1. **Неделя 1:** соберите Project Knowledge + аудит ассетов, скачайте платёжные SVG, начните студийную съёмку товаров.
2. **Неделя 1–2:** генерация Nano Banana по промптам файла 02, background-removal, оптимизация WebP.
3. **Неделя 2:** артефакт **Homepage** → итерации → утверждение.
4. **Неделя 2–3:** артефакты **Catalog + Category** → утверждение.
5. **Неделя 3–4:** артефакт **Product Detail** (самый сложный) → итерации.
6. **Неделя 4:** артефакт **Cart + Checkout** → итерации.
7. **Неделя 5:** артефакт **Account dashboard** + auth страницы.
8. **Неделя 5–6:** артефакты **Trust + Legal + Pruvodce + Blog + Podpora**.
9. **Неделя 6+:** передача в разработку (Claude Code если custom Next.js / фронт-разработчик если CMS-тема).

---

## 8. КУДА ОБРАЩАТЬСЯ ПРИ ПРОБЛЕМАХ

- **Claude отвечает шаблонно** → прикрепите 1–2 свежих скриншота (свои + конкурент), просите «в стиле этих скриншотов».
- **Артефакт сломан / не открывается** → попросите Claude дать упрощённую HTML-only версию (без React) и пошагово восстанавливать.
- **Картинки выглядят неконсистентно** → используйте одинаковый style anchor во всех Nano Banana промптах + одинаковый seed (если API поддерживает).
- **Шрифты не подгружаются с Google Fonts** → используйте Fontsource (npm-пакет) или selfhost (загрузка WOFF2 в /fonts/).
- **Чешские диакритики ломаются** → проверьте `<html lang="cs">` и subset latin-ext в Google Fonts URL.

---

## КОНЕЦ КОМПЛЕКТА (9 файлов)

Все 9 файлов — целостный design-комплект, парный к SEO TZ из предыдущего этапа. Вместе они дают: (а) что строить, (б) с какой типографикой/палитрой, (в) с какими ассетами и где их брать, (г) как продвигать страницы в поиске.

Если нужно расширение: (1) добавлю файлы Figma-токенов (.tokens.json) для импорта в Figma, (2) сделаю детальный prompt-кит для Claude Code для конвертации артефактов в Next.js приложение, (3) подготовлю шаблоны email transactional (заказ принят, отправлен, доставлен, подтверждение возраста при доставке).
