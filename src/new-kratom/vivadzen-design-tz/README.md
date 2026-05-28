# VIVADZEN — Design TZ 2026 (комплект, 10 файлов)

Полный, внедряемый комплект ТЗ для разработки дизайна e-shop Vivadzen. Методические комментарии на русском, on-page тексты, заголовки, кнопки и сообщения — на чешском (готово в продакшн).

Парный к ранее доставленному **SEO TZ комплекту** — оба используются вместе.

## Содержание

| # | Файл | Что внутри | Куда |
|---|---|---|---|
| 0 | `00_WORKFLOW.md` | Как пользоваться комплектом, Claude Design vs Code, Nano Banana, удаление фона, организация ассетов | Прочитать первым |
| 1 | `01_DESIGN_SYSTEM.md` | Палитра (с семантикой и расчётом производных), шрифты Playfair+Inter (полный скейл), spacing, radii, shadows, motion, breakpoints, иконография Lucide, A11y | **В Project Knowledge** |
| 2 | `02_ASSETS_A_NANO_BANANA.md` | Структура папки ассетов, naming, что откуда брать, **готовые промпты для Nano Banana** (9 категорий), post-processing, удаление фона | Выжимка §6 в Project Knowledge |
| 3 | `03_GLOBALNI_KOMPONENTY.md` | Header (mega-menu, поиск, корзина-drawer), Footer (с badges Akreditovaná laboratoř + Autorizovaný prodejce PML), MiniProductCard (с переключателем 25/50 g, цветной кружок vein, Mitragynin %), все buttons, formy, badges, sticky 18+, age-gate, cookie banner | **В Project Knowledge** |
| 4 | `04_STRANKA_HOMEPAGE.md` | Homepage по 11 секциям (с референсами на Image 1/2/3) | Один артефакт |
| 5 | `05_STRANKA_KATALOG_KATEGORIE.md` | Catalog + Category + Strain hub + Search | Один артефакт каждый |
| 6 | `06_STRANKA_PRODUKT.md` | Карточка товара со всеми обязательными блоками PML (warning, Návod, Účinky, Upozornění — дословно), COA-таблица, Q&A, отзывы с фото, подписка | Один артефакт |
| 7 | `07_STRANKA_CHECKOUT_UCET.md` | Корзина, пошаговый чекаут (3 этапа), гость/аккаунт, сохранённые методы, личный кабинет, логин/регистрация | Один артефакт каждый |
| 8 | `08_STRANKA_TRUST_OBSAH_PODPORA.md` | Licence, COA-хаб (с per-šarže страницами), O nás (+ Odborný garant), Prodejny (LocalBusiness), Podpora, Kontakt, Pruvodce hub + статьи, Blog, FAQ, легал | По артефакту на тип |
| 9 | `09_DOD_CHEATSHEET.md` | Definition of Done чек-листы на каждую страницу, шаблоны промптов к Claude, обязательные комплаенс-блоки, быстрая палитра/иконки | Держать рядом |

## Как использовать

### Быстрый старт
1. Прочитать `00_WORKFLOW.md` (15 мин).
2. Создать Project «Vivadzen Design» в claude.ai.
3. Загрузить в Project Knowledge: `01`, `03`, выжимку из `02`, скриншоты Image 1/2/3.
4. Параллельно собрать ассеты по структуре из файла 02 (студийная съёмка товаров + Nano Banana по промптам + платёжные SVG).
5. Для каждой страницы — отдельный чат в Project, прикладываете соответствующий page-TZ (04–08) + просите артефакт по шаблону из файла 09 §2.

### Чему уделить внимание
- **Комплаенс PML:** обязательные warning-блоки на product page копируются **дословно** (см. файл 06 §7 + файл 09 §4).
- **Бейджи:** Akreditovaná laboratoř + Autorizovaný prodejce PML — в footer на каждой странице (файл 03 §2.2).
- **18+:** sticky bar + age-gate без вреда SEO (контент в SSR-HTML, модалка overlay — детали в SEO-файле 6 §6).
- **Mini-карточка:** цветной кружок vein + «Red vein · Borneo» над названием + «Mitragynin 1,42 % · jemně mletý» под названием + переключатель 25/50 g (файл 03 §3).

### Что в следующих итерациях
- Figma design tokens (.tokens.json)
- Промпт-кит для Claude Code (конвертация артефактов в Next.js)
- Шаблоны transactional email (заказ принят, отправлен, доставлен)
- Дизайн админ-панели (если кастомная)

## Связь с SEO TZ

Каждая страница имеет SEO-блок (title/meta/H1/schema), который ссылается на соответствующий пункт SEO TZ (файлы 3.x, 4.x, 5.x, 6.x). При разработке держите оба комплекта открытыми.

## Технологический стек (рекомендация для прототипа)

- **Прототипы:** HTML + Tailwind CDN + Lucide CDN + Google Fonts (Playfair Display + Inter) — артефакты в claude.ai
- **Продакшн (рекомендация):** Next.js 14+ App Router (SSR — критично для возрастного гейта без вреда SEO) + Tailwind + shadcn/ui + Lucide-react
- **Альтернатива:** Shoptet/Shoptet Premium / Shopware 6 — кастомная тема. Менее гибкие, но быстрее для нон-разработчика.
- **Платёжный гейт:** ComGate / GoPay / Stripe (с Apple/Google Pay)
- **Email:** SendGrid / Mailgun
- **Аналитика:** GA4 + Microsoft Clarity (бесплатно) + Seznam Webmaster

## Бренд в одной строке

«Vivadzen — лицензированный кратом, которому можно доверять.» Эстетика премиальная-природная, как Image 1/2/3, с акцентом на лабораторные данные и юридическую чистоту вместо обещаний эффектов. Это и есть отличительное конкурентное преимущество, заложенное в дизайне.
