# VIVADZEN — DESIGN TZ
## Файл 7/9 — Корзина, чекаут, личный кабинет, авторизация

> Конверсионный слой. Здесь — пошаговый чекаут (3 этапа), гость и аккаунт, сохранённые методы, личный кабинет, логин/регистрация, восстановление пароля, успех/ошибка заказа.

---

## 1. КОРЗИНА `/kosik`

### 1.1. Layout
- Header + breadcrumbs: «Domů › Košík»
- Background `paper`
- Container `container`
- Padding 64 vertical / 40 mobile
- Grid: 2 колонки 65/35 desktop (товары / итоги), stacked mobile

### 1.2. H1
- Playfair regular 36 px / 28 mobile: «Váš košík»
- Под H1: «3 položky · 1 040 Kč» (subtitle)
- Справа от H1 (desktop): «← Pokračovat v nákupu» link

### 1.3. Левая колонка — Список товаров

**Каждая строка товара:**
```
┌───────────────────────────────────────────────────────────────┐
│ [фото 96×96]  ● Red vein · Borneo                              │
│               Červená Maeng Da Kratom Prášek                   │
│               Mitragynin 1,42 % · jemně mletý · šarže VD-2026-014│
│               [25 g | 50 g]  ← переключатель                   │
│                                                                 │
│               [−] 1 [+]  ← stepper          490 Kč   [Smazat]  │
└───────────────────────────────────────────────────────────────┘
```

- Card: фон `paper`, border-bottom `border-light` между строками
- Padding 20 vertical / 16 horizontal
- Фото: radius `rounded-md`, фон `cream-soft`
- Переключатель 25/50 g — компактный, как в mini-карточке
- Stepper количества — Global Components §6.7
- Цена обновляется live при изменении кол-ва/балний
- Smazat: ghost-кнопка с icon `trash-2`, при click — confirm-popover «Opravdu smazat?» → undo-toast после удаления (5 сек откатить)

**Если есть подписка в корзине:**
- Над товаром бейдж «Předplatné · každých 30 dní · -10 %» (lime outlined)
- При попытке смешать с разовыми товарами — info: «Předplatné a jednorázové položky budou rozděleny do dvou objednávek»

### 1.4. Под списком — Promo block
- Промокод: input + кнопка «Aplikovat»
  - При успехе — toast + chip «Promo: KRATOM10 · -49 Kč» с × для удаления
- Подарочный сертификат: тот же UX (отдельный input)
- Над promo — «Máte poukaz na slevu? Zadejte kód:»

### 1.5. Правая колонка — Order summary (sticky)

```
┌────────────────────────────────────┐
│  SHRNUTÍ OBJEDNÁVKY                 │ ← eyebrow uppercase
│                                     │
│  Mezisoučet            1 040 Kč    │
│  Doručení               vybráno v dalším kroku   │
│  Sleva (KRATOM10)        -49 Kč    │
│  ───────────────                    │
│  Celkem                  991 Kč    │ ← Inter 700 24 px
│  (vč. DPH 21 %)                     │ ← caption ink-soft
│                                     │
│  [POKRAČOVAT K POKLADNĚ →]          │ ← primary amber, full width, height 56
│                                     │
│  [Visa] [MC] [Apple] [Google] [QR]  │ ← платёжные иконки
│                                     │
│  [icon shield-check] Bezpečné platby SSL · 3D Secure  │
│  [icon refresh-cw] 14 dní na vrácení                  │
│  [icon shield] Vaše údaje neukládáme bez šifrování    │
└────────────────────────────────────┘
```

- Sticky на 80 px от верха при скролле (desktop)
- Mobile: переезжает вниз страницы, кнопка «Pokračovat» — sticky bottom bar

### 1.6. Пустая корзина
- Центрированно: иллюстрация листа (`mitragyna-leaf-single-light.png`) 200×200
- H3: «Váš košík je prázdný»
- Body: «Prohlédněte si naši nabídku kratomu — vše skladem a laboratorně testováno.»
- CTA primary: «Zobrazit kratom →»

---

## 2. ЧЕКАУТ — ОБЩАЯ КОНЦЕПЦИЯ `/pokladna`

### 2.1. Принцип
- **3 этапа** на отдельных «экранах» (или sticky-секциях с прогрессом), не одна простыня:
  1. **Doručení** — куда и как
  2. **Platba** — чем
  3. **Shrnutí a potvrzení** — финальный обзор
- Возможно как **гость**, так и **залогиненный**. После регистрации email прогресс не теряется.
- **Сохранённые методы доставки и оплаты** доступны залогиненному + первой строкой в шаге.
- **Order summary** (правая колонка) на всех шагах виден (sticky).

### 2.2. Header чекаута (упрощённый, без navigation)
- Логотип Vivadzen слева
- Прогресс по центру (3 steps)
- Справа: «Potřebujete pomoc? +420 ... · Live chat» (icon `message-circle`)
- Без mega-menu, без поиска — чтобы не отвлекать

### 2.3. Прогресс-индикатор (desktop)
```
   ●─────────●─────────○
Doručení   Platba   Potvrzení
```
- Active step: круг `amber` filled, иконка-цифра внутри `forest-deep`
- Completed: круг `lime` filled + iconcheck
- Inactive: круг `mist` outlined + цифра `ink-soft`
- Линия между — `border-light`, progress-fill `lime`
- Подписи под кругами: Inter 600 13 px

### 2.4. Layout страницы чекаута
- Background `cream`
- Container `container`
- Grid: 2 колонки 60/40 (форма / order summary sticky) на desktop
- Mobile: одна колонка, order summary collapsable вверху

---

## 3. КРОК 1 — DORUČENÍ

### 3.1. Login/Guest gate (only first time, if not logged in)
- Над формой — выбор:
  ```
  ○ Pokračovat jako host
  ○ Mám účet u Vivadzen  [Přihlásit]
  ○ Vytvořím účet (rychleji při dalším nákupu)
  ```
- Login-link → открывает inline-форму ниже (email + password + «Přihlásit» + «Zapomenuté heslo»)
- «Vytvořím účet» — позже, после введения email (опц. checkbox при registraci)

### 3.2. Контактная информация
- Section header: «Kontaktní údaje»
- Поля (Inter 500 label):
  - E-mail* (для подтверждения заказа и трекинга)
  - Telefon* (для курьера)
  - Помимо checkbox: «Chci dostávat informace o nových šaržích a slevách» (opt-in, NOT pre-checked — GDPR)

### 3.3. Doručovací adresa
- Section header: «Doručovací adresa»

**Если залогинен и есть сохранённые адреса:**
- Cards с сохранёнными адресами (radio):
  ```
  ○ ●Domov  Pavla Nová · Karlovo nám. 5, Praha 2, 12000 · +420 ...
  ○  Práce   ...
  ○  +  Použít novou adresu
  ```
- Click radio — авто-заполнение формы ниже (collapsed)
- «Nová adresa» — раскрывает форму

**Форма адреса (новая):**
- Jméno*
- Příjmení*
- Ulice a číslo popisné*
- Město*
- PSČ*
- Země (default: Česká republika, не редактируется в фазе 1; в фазе 2 расширяем)
- Checkbox: «Uložit jako moji výchozí adresu» (только для залогиненного)

### 3.4. Způsob doručení
- Section header: «Způsob doručení»
- Radio-карточки (каждая — большая):

```
○ [icon truck]  Doručení po ČR — Messenger kurýr
  1–3 pracovní dny                    89 Kč  (zdarma od 1 200 Kč)
  Kurýr ověří váš věk 18+ při převzetí.

○ [icon zap amber] Express 180 minut — Praha a Ostrava
  Doručení do 3 hodin od objednávky    290 Kč
  Dostupné Po–Pá 10:00–18:00. Kurýr ověří věk.

○ [icon store] Osobní odběr — Praha
  [vybrat prodejnu ▾]   Po–Pá 10:00–19:00, So 10:00–14:00   zdarma
  Obvykle do 60 minut od objednávky.

○ [icon truck] Zásilkovna / PPL výdejní místo
  1–2 pracovní dny                    79 Kč
  [vybrat výdejní místo →] (popup widget)
```

- Active radio — border 2 px `forest` + фон `cream-soft`
- При выборе «Osobní odběr» — выпадает select prodejny (2 точки)
- При выборе «Zásilkovna» — кнопка-открыватель widget (популярные карты выдачи) — интеграция со сторонним widget Zásilkovny/PPL
- Под способами доставки — мини-info: «Pro objednávky nad 18+ kurýr/výdej ověří doklad totožnosti.»

### 3.5. Кнопки внизу шага
- Левая: «← Zpět do košíku» (ghost)
- Правая: «Pokračovat k platbě →» (primary amber)

---

## 4. КРОК 2 — PLATBA

### 4.1. Сохранённые методы (если залогинен)
- Section header: «Uložené platební metody»
- Cards (radio):
  ```
  ○ Visa **** 4242 — platí do 12/27
  ○ Apple Pay
  ○ +  Použít novou metodu
  ```
- Click — auto-fill (для card payments — реквизиты сохранены в платёжном провайдере токенизированно, у вас номер не хранится)

### 4.2. Способы оплаты (новые)
- Radio-карточки:

```
○ [icon credit-card] [Visa] [MC] [Apple Pay] [Google Pay]  Platba kartou online
  Bezpečná platba přes 3D Secure
  Token uložen u poskytovatele platby, číslo karty neukládáme.
  [☐] Uložit kartu pro příští platby (jen pro registrované)

○ [icon qr-code] QR platba
  Naskenujte QR kód v aplikaci vaší banky
  
○ [icon banknote] Bankovní převod
  Údaje k převodu obdržíte e-mailem. Objednávku vyřídíme po připsání platby.

○ [icon hand-coins] Dobírka — platba při převzetí
  Hotovostí nebo kartou u kurýra        příplatek 39 Kč
  Není dostupné u Express 180 min.

○ [icon store] Při osobním odběru
  Platba na prodejně (hotovostí nebo kartou)
  Dostupné jen pro «Osobní odběr — Praha»
```

- Active radio — border 2 px `forest` + фон `cream-soft`
- Платёжные иконки — официальные SVG (Visa/MC/Apple/Google Pay brand kits)
- Контекстная доступность: если выбран Express 180, скрыть «Dobírka»; если не выбран Osobní odběr, скрыть «При самовывозе»

### 4.3. Trust block (под способами оплаты)
- Большая info-карточка фон `forest`, color `paper`:
  ```
  [icon shield-check 32 px lime]  Bezpečnost vašich plateb
  
  · 3D Secure 2 ověření u všech kartových transakcí
  · SSL šifrování všech dat při přenosu
  · Údaje karet neukládáme — pouze token poskytovatele platby
  · Vrácení peněz do 14 dní bez udání důvodu
  · GDPR compliant zpracování osobních údajů
  ```
- Padding 24, radius `rounded-xl`

### 4.4. Кнопки внизу шага
- «← Zpět: Doručení»
- «Pokračovat k potvrzení →»

---

## 5. КРОК 3 — POTVRZENÍ

### 5.1. Обзор заказа
- Section header: «Zkontrolujte a potvrďte objednávku»

**Сводка doručení (collapsable, default open):**
```
🚚 Doručení po ČR — Messenger kurýr (1–3 prac. dny)
Pavla Nová · Karlovo nám. 5, Praha 2, 12000
+420 ... · pavla@example.com
[Upravit]
```

**Сводка платby:**
```
💳 Platba kartou online — Visa **** 4242
[Upravit]
```

**Сводка товаров:**
- Список как в корзине (без редактирования), маленький formats

### 5.2. Souhlasy (обязательные чекбоксы)
- [ ] Potvrzuji, že je mi 18 a více let.* (required)
- [ ] Souhlasím s [obchodními podmínkami] a [zpracováním osobních údajů].* (required)
- [ ] Chci dostávat marketingové novinky (volitelné, opt-in)
- [ ] Vím o psychomodulační povaze produktu a četl jsem [bezpečnostní informace na stránce produktu].* (required)

> Это **дополнительная** возрастная верификация. Курьер потом сделает физическую проверку при доставке — это закон.

### 5.3. Финальный CTA
- Кнопка primary `amber` height 60 px width full: «POTVRDIT A ZAPLATIT 991 KČ →»
- Под кнопкой: «Kliknutím přijímáte podmínky a souhlasíte s ověřením věku.»
- При click → редирект на платёжный провайдер ИЛИ inline 3D Secure popup (зависит от платежки)

### 5.4. Loading state
- При обработке — overlay с loading-spinner и текстом «Zpracováváme vaši objednávku...» (не позволять refresh)

---

## 6. СТРАНИЦА УСПЕХА `/objednavka/uspech?id={N}`

### 6.1. Layout
- Background `cream`
- Container `container-narrow`
- Padding 80 vertical

### 6.2. Содержимое
```
[icon check-circle, 64 px, lime-deep, в кружке]

DĚKUJEME!

Objednávka #VZ-2026-1042

Děkujeme za vaši objednávku. Na váš e-mail pavla@example.com jsme
odeslali potvrzení s detaily a údaji k platbě.

[Card s доставка info: kurýr, adresa, ETA]

[Card s платежной info: status «Čeká na platbu» / «Zaplaceno»]

[Kód pro sledování (až bude k dispozici): Sledovat zásilku →]

— Co dál? —
1. Obdržíte e-mail s potvrzením.
2. (pokud bankovní převod) — pošlete platbu na uvedený účet.
3. Připravíme objednávku a kurýr vás bude kontaktovat.

[Pokračovat v nákupu]   [Zobrazit moje objednávky]  ← (если есть аккаунт)
```

- H1 — Playfair italic 56 px, color `forest`
- Order ID — Inter 700 24 px, color `terracotta`

### 6.3. Если гость
- Под success-блоком — приглашение зарегистрироваться:
  ```
  Vytvořte si účet pro snadnější sledování budoucích objednávek
  [Vytvořit účet z této objednávky →]   ← одним кликом, password setup
  ```

### 6.4. Cross-sell
- Внизу: «Související produkty» 4 MiniProductCard

---

## 7. СТРАНИЦА ОШИБКИ ЗАКАЗА `/objednavka/chyba`

- icon `alert-circle` 64 px `danger`
- H1: «Něco se pokazilo»
- Body: «Vaši platbu se nepodařilo zpracovat. Žádné peníze nebyly strženy.»
- Возможные причины (info-карточка с bullet-list)
- CTA: «Zkusit znovu» (primary) / «Zvolit jiný způsob platby» (outline) / «Kontaktovat podporu» (ghost)

---

## 8. ЛИЧНЫЙ КАБИНЕТ `/ucet`

### 8.1. Layout
- Header (full nav как обычно)
- Background `paper`
- Container `container`
- Grid: 2 колонки 25/75 (sidebar / контент)
- Mobile: верхний tab-row + контент ниже

### 8.2. Sidebar (sticky)
```
●Avatar (initials в lime круге)
Pavla N.
pavla@example.com

NAVIGACE
[icon layout-dashboard] Přehled
[icon shopping-bag] Moje objednávky (12)
[icon repeat] Předplatné (1 aktivní)
[icon map-pin] Adresy
[icon credit-card] Platební metody
[icon heart] Oblíbené (3)
[icon star] Recenze a otázky
[icon bell] Notifikace
[icon settings] Nastavení účtu

[icon log-out] Odhlásit se
```

- Active item: фон `cream-soft`, border-left 3 px `amber`, text `ink`
- Inactive: text `ink-soft`, hover `cream-soft`
- Card padding 24, radius `rounded-xl`

### 8.3. Přehled (default страница)

**Top stats (3 карточки):**
- 12 objednávek
- 1 aktivní předplatné
- 3 ⭐ napsané recenze

**Последний заказ:**
- Card с deталями последнего заказа: дата, статус, сумма, список товаров миниатюрно
- «Sledovat zásilku →» / «Opakovat objednávku →»

**Активная подписка (если есть):**
- Card s subscription details: товар, интервал, следующая дата, статус, кнопки «Posunout» / «Pozastavit» / «Spravovat»

**Saved methods quick view:**
- Sidebar mini-block: «2 uložené adresy · 1 platební karta»

**Recommendations:**
- 4 рекомендованных товара (mini-карточки)

### 8.4. Moje objednávky `/ucet/objednavky`
- Список заказов (карточки):
  ```
  #VZ-2026-1042   12. 03. 2026     991 Kč    [Status chip]
  3 položky · Express 180 min · Visa **** 4242
  [Detail objednávky] [Sledovat] [Opakovat]
  ```
- Status chips:
  - `Čeká na platbu` (warning amber)
  - `Zaplaceno` (lime)
  - `Připravuje se` (info blue)
  - `Odesláno` (lime-deep)
  - `Doručeno` (lime solid)
  - `Stornováno` (mist)
- Фильтры: Все / Doručeno / Aktivní / Stornováno
- Поиск по order ID

### 8.5. Detail objednávky `/ucet/objednavky/{id}`
- Все детали заказа: товары, адрес, способ доставки/оплаты, timeline статусов
- «Stáhnout fakturu (PDF)»
- «Stáhnout COA všech položek (ZIP)» — все COA в одном архиве (UX-плюс уникальный)
- «Reklamace» — открывает форму
- «Opakovat objednávku» — добавляет всё в корзину

### 8.6. Předplatné `/ucet/predplatne`
- Список активных и приостановленных подписок
- Каждая — карточка:
  ```
  Červená Maeng Da · 50 g · každých 30 dní · -10 %
  Příští dodání: 15. 06. 2026 · 441 Kč
  [Status: Aktivní]
  
  [Posunout dodání] [Pozastavit] [Změnit interval] [Změnit balení] [Zrušit]
  ```
- При click «Spravovat» — раскрывается form

### 8.7. Adresy `/ucet/adresy`
- Список сохранённых адресов (cards)
- Default address — бейдж «Výchozí»
- Кнопки: «Upravit» / «Smazat» / «Nastavit jako výchozí»
- «+ Přidat adresu» CTA

### 8.8. Platební metody `/ucet/platby`
- Список карт (токены, последние 4 цифры, expiry)
- Apple Pay / Google Pay — если активны
- «+ Přidat platební metodu» — открывает форму платёжного провайдера (Stripe Setup Intent / Comgate Save Card)

### 8.9. Oblíbené (wishlist)
- Mini-карточки с heart filled
- Кнопки «Do košíku» / «Odebrat z oblíbených»

### 8.10. Recenze a otázky
- Список написанных пользователем
- Статус модерации каждого: «Publikováno» / «Čeká na schválení» / «Skryto modеrátorem»
- Возможность редактировать / удалять (в пределах политики)

### 8.11. Notifikace
- Toggle-list: какие notifikace получать (e-mail/SMS)
- Категории: Status objednávky · Nové šarže · Předplatné · Marketing

### 8.12. Nastavení účtu
- Изменение email / телефона / пароля
- Двухфакторная (опц., рекомендую внедрить как stretch)
- Smazat účet (GDPR — право на стирание)

---

## 9. РЕГИСТРАЦИЯ `/registrace`

### 9.1. Layout
- Centered, max-width 480
- Background `paper`
- Card `rounded-xl` shadow-card padding 40

### 9.2. Содержимое
- Логотип Vivadzen наверху
- H1 Playfair regular: «Vytvořit účet»
- Body: «Rychlejší pokladna, sledování objednávek a předplatné.»
- Form:
  - Jméno*
  - Příjmení*
  - E-mail*
  - Heslo* (with strength indicator, min 8 chars)
  - Confirm heslo*
  - Checkbox: Souhlasím s [obchodními podmínkami] a [GDPR]* (required)
  - Checkbox: Chci dostávat marketingové novinky (opt-in, NOT pre-checked)
  - Checkbox: Potvrzuji, že jsem starší 18 let* (required)
- CTA primary: «Vytvořit účet»
- Pod tlačítkem: «Máte už účet? [Přihlásit]»

### 9.3. После регистрации
- Email-verification: «Odeslali jsme vám potvrzovací e-mail. Klikněte na odkaz pro aktivaci.»
- До верификации — limited access (можно смотреть, но не покупать с аккаунта)

---

## 10. ВХОД `/prihlaseni`

- Centered card
- H1 «Přihlášení»
- Form:
  - E-mail*
  - Heslo*
  - Checkbox «Zůstat přihlášen» (default unchecked)
- CTA primary: «Přihlásit se»
- Pod: «Zapomněli jste heslo? [Obnovit]» · «Nemáte účet? [Vytvořit]»
- Опционально (фаза 2): SSO кнопки «Pokračovat přes Google / Apple» (но это требует extra OAuth setup; не критично для MVP)

---

## 11. ВОССТАНОВЛЕНИЕ ПАРОЛЯ `/obnoveni-hesla`

- Step 1: ввод email → отправка ссылки
- Step 2: click из email → форма «Nové heslo» + confirm
- Step 3: success → редирект на login

---

## 12. SEO И TECHNICAL

- Чекаут/корзина/аккаунт — `noindex,nofollow` (см. SEO-файл 6 §3 матрицу индексации)
- Логин/регистрация — `noindex,follow`
- Все эти страницы — HTTPS, CSP-strict, no inline-eval

---

## 13. EDGE CASES

| Случай | Поведение |
|---|---|
| Корзина пустая, кто-то открыл /pokladna | Редирект на /kosik |
| Незалогиненный заходит в /ucet | Редирект на /prihlaseni?return=/ucet |
| Истёкшая сессия | Modal «Vaše přihlášení vypršelo. Přihlaste se znovu.» |
| Дубликат email при регистрации | Inline error «Tento e-mail je již registrován» + link «Přihlásit / Obnovit heslo» |
| Платёж отклонён | Редирект на /objednavka/chyba (см. §7) с сохранением корзины |
| Возраст-чекбокс не отмечен | Submit disabled, hint поверх кнопки |
| Express 180 min выбран, но адрес не в Praha/Ostrava | Inline warning + опция вернуться к Doručení |

→ Дальше — файл 08 (Trust + контент + поддержка).
