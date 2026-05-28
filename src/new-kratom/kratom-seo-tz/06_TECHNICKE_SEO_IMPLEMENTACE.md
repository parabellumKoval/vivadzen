# VIVADZEN — SEO ТЗ
## Файл 6/7 — Техническое SEO и комплаенс-имплементация

> Это раздел для разработчика. Каждый пункт — внедряемый. Шаблоны JSON-LD — копипаст с заменой `{плейсхолдеров}`.

---

## 1. ДОМЕН, ГЕО, ЯЗЫК

- Сайт чешский, гео = ЧР. Рекомендация по приоритету:
  1. **Идеально:** primary-домен `.cz` (vivadzen.cz) — сильнейший локальный сигнал в Google.cz. Если бренд на `vivadzen.com` — рассмотреть миграцию или `.cz` как primary с 301.
  2. **Минимум (если остаёмся на .com):** в GSC задать гео-таргетинг = Czechia; `<html lang="cs">`; hreflang только если есть др. языковые версии.
- `hreflang`: если только чешская версия — hreflang не нужен; если добавится EN/др. — `x-default` + `cs-CZ`.
- HTTPS обязателен, HSTS, единый хост: выбрать `https://домен/` без `www` (или с — но один), 301 со всех вариантов; trailing slash — единообразно; нижний регистр URL.

---

## 2. ПРАВИЛА URL

- ЧПУ, латиница+чешские слова в slug без диакритики (`zelena-maeng-da`), дефисы, без подчёркиваний, без `?id=`, без stop-параметров в индексируемых URL.
- Категории: `/kratom/{cvet|strain}`; типы: `/kratom-prasek`, `/kratom-extrakt` (top-level); товары: текущие slug сохранить + 301 со старых `/catalog/*`.
- Фасовка 25/50 g = **один URL + вариант**, не два URL.
- Глубина ≤ 3 клика от `/`.

---

## 3. ИНДЕКСАЦИЯ — МАТРИЦА

| Тип | robots meta | В sitemap | canonical |
|---|---|---|---|
| `/`, категории, типы, strain-хабы, товары live, pillar/гайды, энц., trust, local, /caste-dotazy, /blog/* | `index,follow` | да | self |
| Placeholder-товары с ≥350 слов уник. контента | `index,follow` | да | self |
| Placeholder без контента (ещё не наполнен) | `noindex,follow` | нет | — |
| Фильтры/сортировки (`?barva=`, `?sort=`, `?cena=`) | `index,follow` но **canonical → чистая категория** | нет | на категорию |
| Пагинация `/kratom?page=2` | `index,follow`, self-canonical (НЕ на стр.1), уникальный title с «– strana 2» | нет (или да, по желанию) | self |
| Внутр. поиск `/?q=` | `noindex,follow` | нет | — |
| Корзина/checkout/účet | `noindex,nofollow` | нет | — |
| Легал (`/obchodni-podminky` и т.п.) | `index,follow` | опц. (низкий prio) | self |
| Страницы šarže `/laboratorni-testy/{id}` | `index,follow` (если есть текст-расшифровка) | да | self |
| Тег/служебные архивы без ценности | `noindex,follow` | нет | — |

> **Никаких `noindex` на возрастном гейте** (см. §6) и никакого блока Googlebot.

---

## 4. ROBOTS.TXT (шаблон)

```
User-agent: *
Allow: /
Disallow: /kosik
Disallow: /pokladna
Disallow: /ucet
Disallow: /*?q=
Disallow: /*?sort=
Disallow: /*?razeni=
Disallow: /*&
Allow: /*?barva=        # фильтры пускаем (canonical разрулит), НЕ блокируем — иначе потеряем сигналы
Sitemap: https://{domen}/sitemap.xml
```
> Не использовать `Disallow` для управления дублями — для этого `canonical`/`noindex`. `Disallow` только для технических зон (корзина и т.п.). Закрытая `Disallow`-страница не передаёт canonical (Google её не прочитает) — поэтому фильтры НЕ закрываем в robots.

---

## 5. SITEMAP.XML

- Индексный `sitemap.xml` → под-карты: `sitemap-pages.xml`, `sitemap-products.xml`, `sitemap-categories.xml`, `sitemap-guides.xml`, `sitemap-blog.xml`, `sitemap-images.xml`.
- Только `index,follow` + 200 OK + self-canonical URL. `lastmod` реальный (на товарах меняется при смене šarže/цены).
- Обновлять автоматически при публикации/смене статуса. Сабмит в GSC + Seznam Webmaster (Seznam.cz/Bing — для CZ-рынка не игнорировать).

---

## 6. ВОЗРАСТНОЙ ГЕЙТ 18+ БЕЗ ВРЕДА SEO ⚠️ (критично)

Закон требует 18+ верификацию (двойную). Но **классическая модалка-«заглушка», которая прячет контент или редиректит до подтверждения, убивает SEO** (Googlebot видит пустоту/редирект). Правильная реализация:

**Принцип:** контент **всегда в DOM и доступен Googlebot и пользователю в исходном HTML** (SSR). Возрастной баннер — **наложение поверх**, управляется cookie, **не cloaking** (Googlebot видит то же, что пользователь до клика).

Реализация:
1. Контент рендерится server-side полностью (SSR/SSG), присутствует в HTML-ответе 200.
2. Поверх — модальное overlay `position:fixed` с «Je vám 18+? Ano/Ne». Это **CSS-наложение**, не замена контента.
3. Выбор «Ano» → cookie `age_verified=1` (1 год) → overlay скрывается. «Ne» → информационная страница/выход.
4. **Не делать**: redirect на `/age-gate` до подтверждения; `display:none` всего body; разный HTML для бота и юзера (это cloaking → бан).
5. Для покупки — **юридическая** верификация (чекбокс «потвржую 18+» в чекауте + верификация курьером/на точке при выдаче — двойная по закону). SEO-гейт ≠ юр-верификация; нужны оба, но SEO-гейт не должен прятать контент от индексации.
6. На товарных/контентных страницах оставить видимый постоянный бейдж «18+» и PML-строку (это и комплаенс, и trust-сигнал).

> Итог: Google индексирует весь контент; пользователь видит 18+ подтверждение; закон соблюдён через чекаут+курьера. Это обходит ошибку 90% конкурентов (у многих age-gate режет индексацию).

---

## 7. SCHEMA.ORG JSON-LD (шаблоны — внедрить в `<head>` или конец `<body>`)

### 7.1. Organization (на всех страницах, глобально)
```json
{
  "@context":"https://schema.org",
  "@type":"Organization",
  "name":"Vivadzen",
  "url":"https://{domen}/",
  "logo":"https://{domen}/logo.png",
  "description":"Specializovaný licencovaný e-shop s kratomem napojený na kamenné prodejny v Praze.",
  "address":[
    {"@type":"PostalAddress","streetAddress":"{ulice prodejny 1}","addressLocality":"Praha","postalCode":"{psc}","addressCountry":"CZ"},
    {"@type":"PostalAddress","streetAddress":"{ulice prodejny 2}","addressLocality":"Praha","postalCode":"{psc}","addressCountry":"CZ"}
  ],
  "contactPoint":{"@type":"ContactPoint","telephone":"+420{tel}","contactType":"customer service","areaServed":"CZ","availableLanguage":"cs"},
  "sameAs":["{FB}","{IG}","{Heureka profil}"]
}
```

### 7.2. WebSite + SearchAction (на `/`)
```json
{"@context":"https://schema.org","@type":"WebSite","name":"Vivadzen","url":"https://{domen}/",
 "potentialAction":{"@type":"SearchAction","target":"https://{domen}/?q={search_term_string}","query-input":"required name=search_term_string"}}
```

### 7.3. Product + Offer (товарные карточки live; 25/50 g — 2 Offer)
```json
{
 "@context":"https://schema.org","@type":"Product",
 "name":"{Název produktu}",
 "image":["https://{domen}/img/{produkt}-1.jpg"],
 "description":"{Faktický popis – původ, zpracování, žilka. BEZ tvrzení o účincích.}",
 "sku":"{SKU}","brand":{"@type":"Brand","name":"Vivadzen"},
 "category":"Kratom > {Barva} > {Odrůda}",
 "offers":[
  {"@type":"Offer","url":"https://{domen}/{slug}","priceCurrency":"CZK","price":"{cena25}","itemCondition":"https://schema.org/NewCondition","availability":"https://schema.org/InStock","name":"25 g"},
  {"@type":"Offer","url":"https://{domen}/{slug}","priceCurrency":"CZK","price":"{cena50}","itemCondition":"https://schema.org/NewCondition","availability":"https://schema.org/InStock","name":"50 g"}
 ]
 /* AggregateRating/Review ДОБАВЛЯТЬ ТОЛЬКО при реальных отзывах */
}
```
> ⚠️ **Никаких** свойств здоровья/эффекта в schema (нет `MedicalSubstance`, нет «benefits»). Только товарные/коммерческие поля. Цена/наличие в schema = реальные на странице (иначе Merchant/Rich-results ошибки).

### 7.4. Placeholder (до лицензии) — БЕЗ Product/Offer
```json
{"@context":"https://schema.org","@type":"Article",
 "headline":"{Název odrůdy} kratom — odrůda a charakteristika",
 "author":{"@type":"Person","name":"{Garant}"},
 "publisher":{"@type":"Organization","name":"Vivadzen"},
 "dateModified":"{ISO}"}
```
После лицензии → апгрейд до 7.3 с `availability` (`InStock`/`OutOfStock`).

### 7.5. BreadcrumbList (все небрендовые страницы)
```json
{"@context":"https://schema.org","@type":"BreadcrumbList","itemListElement":[
 {"@type":"ListItem","position":1,"name":"Domů","item":"https://{domen}/"},
 {"@type":"ListItem","position":2,"name":"Kratom","item":"https://{domen}/kratom"},
 {"@type":"ListItem","position":3,"name":"{Barva}","item":"https://{domen}/kratom/{cvet}"},
 {"@type":"ListItem","position":4,"name":"{Produkt}"}
]}
```

### 7.6. FAQPage (страницы с FAQ-блоком)
```json
{"@context":"https://schema.org","@type":"FAQPage","mainEntity":[
 {"@type":"Question","name":"{Otázka cs}","acceptedAnswer":{"@type":"Answer","text":"{Odpověď cs}"}}
]}
```
> FAQ-разметка должна 1:1 совпадать с видимым текстом на странице (иначе manual action). По одной FAQPage на страницу.

### 7.7. LocalBusiness (под-страницы магазинов)
```json
{"@context":"https://schema.org","@type":"Store",
 "name":"Vivadzen — prodejna Praha {lokalita}",
 "image":"https://{domen}/img/prodejna-{n}.jpg",
 "address":{"@type":"PostalAddress","streetAddress":"{ulice}","addressLocality":"Praha","postalCode":"{psc}","addressCountry":"CZ"},
 "geo":{"@type":"GeoCoordinates","latitude":{lat},"longitude":{lng}},
 "telephone":"+420{tel}","priceRange":"$$","areaServed":"Praha",
 "openingHoursSpecification":[{"@type":"OpeningHoursSpecification","dayOfWeek":["Monday","Tuesday","Wednesday","Thursday","Friday"],"opens":"{hh:mm}","closes":"{hh:mm}"}],
 "url":"https://{domen}/prodejny/praha-{lokalita}"}
```

### 7.8. Article (pillar/гайды/энц.)
```json
{"@context":"https://schema.org","@type":"Article",
 "headline":"{H1}","author":{"@type":"Person","name":"{Garant}","jobTitle":"{role}","url":"https://{domen}/o-nas"},
 "publisher":{"@type":"Organization","name":"Vivadzen","logo":{"@type":"ImageObject","url":"https://{domen}/logo.png"}},
 "datePublished":"{ISO}","dateModified":"{ISO}","mainEntityOfPage":"https://{domen}/{slug}"}
```

> Все JSON-LD валидировать в Google Rich Results Test + Schema.org validator перед релизом. Не дублировать одну сущность дважды на странице.

---

## 8. PLACEHOLDER / OUT-OF-STOCK ЛОГИКА (SEO + комплаенс)

- **До лицензии:** страница = `Article`/`ItemPage`, **нет цены, нет кнопки «do košíku»**, статус «Momentálně nedostupné — připravujeme», CTA «Upozornit při naskladnění» (e-mail, double opt-in, GDPR). `index,follow` при ≥350 слов уник. контента.
- **После лицензии, товар временно нет на складе:** `Product`+`Offer` с `availability: OutOfStock`, страница НЕ `noindex` (сохраняем equity), кнопка «Upozornit», предложить альтернативы (related). Никогда не 404/410 живой товар, который вернётся — это потеря рейтинга.
- **Снятый навсегда товар:** 301 на ближайшую категорию/аналог (не «мягкий 404»).
- Никогда не оставлять out-of-stock без альтернатив (UX + crawl-бюджет).

---

## 9. ПАГИНАЦИЯ И ФИЛЬТРЫ

- Пагинация: каждая страница self-canonical, уникальный `<title>`/`<h1>` с « – strana N», `rel=prev/next` (как UX-хинт, Google его не использует для индексации, но не вредит), в sitemap — опц.
- Фильтры (цвет/штамм/цена/наличие): URL с параметрами → `canonical` на чистую категорию; **SEO-ценные срезы (цвет) вынесены в статические URL-категории**, не в параметры. Не плодить индексируемые фасетные комбинации (фасетный взрыв = crawl-бюджет + дубли).
- Сортировки: всегда `canonical` на дефолт.

---

## 10. CORE WEB VITALS / ПРОИЗВОДИТЕЛЬНОСТЬ

- Цели: LCP < 2.5s, INP < 200ms, CLS < 0.1 (mobile-first).
- SSR/SSG для контента (особенно из-за age-gate — контент в HTML-ответе).
- Изображения: WebP/AVIF, `width/height` заданы (anti-CLS), `loading=lazy` для below-the-fold, `fetchpriority=high` для LCP-hero, responsive `srcset`.
- Шрифты: `font-display:swap`, preload основного, сабсет латиница+чешская диакритика.
- Критический CSS инлайн, остальное defer; JS минимизировать, без рендер-блокеров; сторонние скрипты (чат/аналитика) — async/defer/lazy.
- CDN, Brotli, HTTP/2+; кэш-заголовки на статике.
- Мобильная версия = первичная (Google mobile-first). Тапы ≥48px, без интерстишелов кроме legal age-gate (Google допускает legal/age интерстишел — но реализованный как §6, не блокирующий контент).

---

## 11. ИЗОБРАЖЕНИЯ / МЕДИА

- `alt` фактологический, без эффект-обещаний: `alt="Zelená Maeng Da kratom prášek balení 25 g Vivadzen"`.
- Имена файлов ЧПУ: `zelena-maeng-da-kratom-prasek-25g.webp`.
- `sitemap-images.xml`; на товарах ≥3 фото (упаковка, порошок, этикетка с обязательной маркировкой §33e — это и комплаенс, и trust).
- COA-сканы: `ImageObject` + **обязательно текстовая расшифровка значений рядом** (Google не читает PDF-картинку).

---

## 12. БЕЗОПАСНОСТЬ / ДОВЕРИЕ (тех. сигналы E-E-A-T)

- Валидный SSL, без mixed-content.
- Видимые: IČO/DIČ, юр. лицо, адреса 2 точек, телефон, e-mail (NAP консистентность с GBP и schema).
- Страницы `/licence`, `/laboratorni-testy`, `/o-nas` (garant), `/obchodni-podminky`, `/ochrana-soukromi` слинкованы из footer на каждой странице.
- Cookie-consent (GDPR/ePrivacy) — не блокирует Googlebot, не прячет контент.
- Без агрессивных попапов поверх контента (кроме legal age-gate по §6).

---

## 13. АНАЛИТИКА / МОНИТОРИНГ

- GSC + Seznam Webmaster + Bing Webmaster (CZ: Seznam.cz — не игнорировать, заметная доля).
- GA4 (или privacy-friendly альтернатива) + серверные события заказа.
- Логи/краулинг: Screaming Frog ежемес. (битые ссылки, дубли title/desc, orphan-страницы, глубина).
- Мониторинг: позиции по ядру (Collabim/Marketing Miner — CZ), индексация placeholder/COA, Rich Results статус, CWV (CrUX/PSI), 404/5xx, потеря 301.
- Алерты на: падение индексации, рост 404, ручные санкции, ошибки schema.

---

## 14. ЧЕК-ЛИСТ ПЕРЕД РЕЛИЗОМ (Definition of Done постранично)

- [ ] Уникальные title (≤60) и meta description (≤155), без effect-claims
- [ ] Один H1, логичная иерархия H2/H3
- [ ] Контент ≥ целевого объёма (файлы 2–5), уникальный, комплаенс-чистый
- [ ] Внутр. ссылки ≥ норматива, осмысленные анкоры
- [ ] FAQ-блок + FAQPage schema (где предусмотрено), текст 1:1
- [ ] BreadcrumbList + профильная schema, валидны в Rich Results Test
- [ ] Бейдж 18+ + PML-строка + дисклеймер + ссылки /licence,/laboratorni-testy
- [ ] canonical корректный, robots-meta по матрице, в sitemap (если index)
- [ ] Изображения WebP, alt, размеры, lazy/priority
- [ ] CWV в зелёной зоне на mobile
- [ ] Age-gate не режет индексацию (§6), SSR-контент в HTML 200
- [ ] 301 со старых `/catalog/*` (где применимо), нет цепочек редиректов

→ Переходим к файлу 7 (дорожная карта, приоритеты, KPI).
