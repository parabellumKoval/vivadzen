const fallbackMessages: Record<string, string> = {
    'nav.dashboard': 'Панель',
    'nav.products': 'Товары',
    'nav.lab_batches': 'Лаб-тесты',
    'nav.taxonomies': 'Таксономии',
    'nav.orders': 'Заказы',
    'nav.users': 'Клиенты',
    'nav.media': 'Медиа',
    'layout.warm_cache': 'Прогреть Redis',
    'layout.warming_cache': 'Прогрев…',
    'layout.open_site': 'Открыть сайт',
    'layout.logout': 'Выйти',
    'layout.content_locale': 'Язык контента',
    'dashboard.title': 'Панель управления',
    'dashboard.loading': 'Загрузка…',
    'dashboard.products': 'Товары',
    'dashboard.orders_today': 'Заказы сегодня',
    'dashboard.orders_month': 'Заказы за месяц',
    'dashboard.revenue_month': 'Оборот за месяц',
    'dashboard.pending_orders': 'В обработке',
    'dashboard.recent_orders': 'Последние 10 заказов',
    'dashboard.all_orders': 'Все заказы',
    'dashboard.no_orders': 'Заказов пока нет',
    'products.title': 'Товары',
    'products.new_product': 'Новый товар',
    'products.search_placeholder': 'Поиск по slug или партии…',
    'products.slug': 'Slug',
    'products.name': 'Название',
    'products.color': 'Цвет',
    'products.strain': 'Сорт',
    'products.mitragynin': 'Митрагинин',
    'products.batch': 'Партия',
    'products.stock': 'Наличие',
    'products.in_stock': 'В наличии',
    'products.out_of_stock': 'Нет в наличии',
    'products.empty': 'Товары не найдены',
    'products.previous': '← Назад',
    'products.next': 'Далее →',
    'products.save': 'Сохранить',
    'products.saving': 'Сохранение…',
    'products.delete': 'Удалить',
    'products.delete_confirm': 'Удалить товар?',
    'products.save_error': 'Не удалось сохранить товар',
    'products.basic': 'Основное',
    'products.content': 'Контент',
    'products.classification': 'Классификация',
    'products.lab': 'Лабораторные данные',
    'products.image': 'Изображение',
    'products.variants': 'Варианты и цена',
    'products.short': 'Краткое описание',
    'products.description': 'Описание',
    'products.origin': 'Происхождение',
    'products.grind': 'Тип помола',
    'products.form': 'Форма',
    'products.stock_checkbox': 'Есть в наличии',
    'products.tested_at': 'Дата теста',
    'products.main_image': 'URL главного изображения',
    'products.add_variant': '+ Добавить вариант',
    'products.size': 'Размер',
    'products.unit': 'Единица',
    'products.price': 'Цена (CZK)',
    'products.sku': 'SKU',
    'products.position': 'Позиция',
    'products.published_at': 'Дата публикации',
    'products.select_empty': '—',
    'taxonomies.title': 'Таксономии',
    'taxonomies.add': 'Добавить',
    'taxonomies.edit': 'Редактировать',
    'taxonomies.delete': 'Удалить',
    'taxonomies.delete_confirm': 'Удалить эту таксономию?',
    'taxonomies.new': 'Новая таксономия',
    'taxonomies.slug': 'Slug',
    'taxonomies.label': 'Название',
    'taxonomies.description': 'Описание',
    'taxonomies.position': 'Позиция',
    'taxonomies.cancel': 'Отмена',
    'taxonomies.save': 'Сохранить',
    'taxonomies.meta': 'Параметры',
    'taxonomies.h1': 'H1 категории',
    'taxonomies.origin': 'Страна / регион',
    'taxonomies.dose': 'Рекомендуемая дозировка',
    'taxonomies.sub': 'Подзаголовок',
    'taxonomies.vein': 'Тип жилки',
    'taxonomies.accent': 'Акцент темы',
    'taxonomies.range_min': 'Мин. митрагинин',
    'taxonomies.range_max': 'Макс. митрагинин',
    'taxonomies.coming_soon': 'Скоро в продаже',
    'taxonomies.types.color': 'Цвет',
    'taxonomies.types.strain': 'Сорт',
    'taxonomies.types.form': 'Форма',
    'taxonomies.types.region': 'Регион',
    'orders.title': 'Заказы',
    'orders.search_placeholder': 'ID, e-mail, телефон…',
    'orders.all_statuses': 'Все статусы',
    'orders.customer': 'Клиент',
    'orders.email': 'E-mail',
    'orders.status': 'Статус',
    'orders.items': 'Позиций',
    'orders.total': 'Сумма',
    'orders.time': 'Время',
    'orders.empty': 'Заказы не найдены',
    'orders.items_title': 'Состав заказа',
    'orders.customer_title': 'Клиент',
    'orders.delivery': 'Доставка',
    'orders.payment': 'Оплата',
    'orders.change_status': 'Сменить статус',
    'orders.select_status': '— выберите статус —',
    'orders.note_placeholder': 'Комментарий (необязательно)',
    'orders.history': 'История',
    'orders.subtotal': 'Подытог',
    'orders.discount': 'Скидка',
    'orders.back': 'Назад',
    'users.title': 'Клиенты',
    'users.search_placeholder': 'Имя, e-mail, телефон…',
    'users.all': 'Все',
    'users.active': 'Активные',
    'users.blocked': 'Заблокирован',
    'users.unverified': 'Не подтверждён',
    'users.verified': 'Подтверждён',
    'users.name': 'Имя',
    'users.email': 'E-mail',
    'users.phone': 'Телефон',
    'users.orders': 'Заказы',
    'users.reviews': 'Отзывы',
    'users.status': 'Статус',
    'users.registered': 'Регистрация',
    'users.empty': 'Клиенты не найдены',
    'users.profile': 'Профиль',
    'users.marketing': 'Маркетинговые рассылки',
    'users.save': 'Сохранить',
    'users.saving': 'Сохранение…',
    'users.save_error': 'Не удалось сохранить',
    'users.block': 'Заблокировать',
    'users.unblock': 'Разблокировать',
    'users.delete': 'Удалить',
    'users.delete_confirm': 'Удалить этого клиента? Действие необратимо.',
    'users.meta': 'Сведения',
    'users.email_status': 'E-mail',
    'users.has_password': 'Пароль',
    'users.social': 'Соцсети',
    'users.addresses': 'Адреса',
    'users.default': 'по умолчанию',
    'users.no_orders': 'Заказов нет',
    'users.no_reviews': 'Отзывов нет',
    'users.skip_age_verification': 'Отключить проверку возраста (ADULTO)',
    'users.skip_age_verification_hint': 'Если включено — этому клиенту в чекауте не будет показываться виджет ADULTO и проверка возраста на сервере пропускается.',
    'media.title': 'Медиа',
    'media.upload': 'Загрузить',
    'media.uploading': 'Загрузка…',
    'media.empty': 'Библиотека пуста. Загрузите изображения.',
    'media.delete_confirm': 'Удалить файл?',
    'auth.title': 'Панель администратора',
    'auth.email': 'E-mail',
    'auth.password': 'Пароль',
    'auth.login': 'Войти',
    'auth.logging_in': 'Вход…',
    'auth.login_error': 'Не удалось войти',
    'common.total_range': ':from–:to из :total',
    'lab_batches.title': 'Лабораторные тесты (COA)',
    'lab_batches.new': 'Новый тест',
    'lab_batches.search_placeholder': 'Поиск по lot или товару…',
    'lab_batches.empty': 'Тесты ещё не добавлены',
    'lab_batches.lot': 'Lot',
    'lab_batches.product_name': 'Продукт (для COA)',
    'lab_batches.strains': 'Сорта',
    'lab_batches.add_strain': 'Добавить',
    'lab_batches.package': 'Упаковка',
    'lab_batches.mass': 'Масса',
    'lab_batches.lab_name': 'Лаборатория',
    'lab_batches.received_at': 'Дата отбора',
    'lab_batches.issued_at': 'Протокол выдан',
    'lab_batches.published_at': 'Публикация',
    'lab_batches.files': 'PDF',
    'lab_batches.products': 'Товары',
    'lab_batches.pass_ratio': 'Прошло',
    'lab_batches.meta': 'Параметры партии',
    'lab_batches.linked_products': 'Связанные товары',
    'lab_batches.linked_products_hint': 'Эти товары будут показывать данные этой партии на сайте.',
    'lab_batches.add_row': 'Добавить строку',
    'lab_batches.row_name': 'Параметр',
    'lab_batches.row_symbol': 'Симв.',
    'lab_batches.row_value': 'Значение',
    'lab_batches.row_uncertainty': '± Погр.',
    'lab_batches.row_unit': 'Ед.',
    'lab_batches.row_limit': 'Лимит',
    'lab_batches.row_status': 'Статус',
    'lab_batches.protocols': 'Оригинальные PDF-протоколы',
    'lab_batches.protocols_hint': 'Несколько PDF на партию: например отдельно мика, отдельно тяжёлые металлы.',
    'lab_batches.file_no': '№ протокола',
    'lab_batches.file_label': 'Раздел',
    'lab_batches.file_date': 'Дата',
    'lab_batches.file': 'Файл',
    'lab_batches.upload_pdf': 'Загрузить PDF',
    'lab_batches.uploading': 'Загрузка…',
    'lab_batches.save_first_for_files': 'Сначала сохраните партию, затем можно будет загрузить PDF.',
    'lab_batches.delete': 'Удалить',
    'lab_batches.delete_confirm': 'Удалить партию вместе со всеми PDF? Действие необратимо.',
    'lab_batches.open_public': 'Открыть на сайте',
    'status.pending': 'Новый',
    'status.received': 'Принят',
    'status.paid': 'Оплачен',
    'status.packed': 'Упакован',
    'status.shipped': 'Отправлен',
    'status.delivered': 'Доставлен',
    'status.cancelled': 'Отменён',
    'status.refunded': 'Возврат',
    'nav.pruvodce': 'Průvodce',
    'pruvodce.title': 'Wiki články',
    'pruvodce.new': 'Nový článek',
    'pruvodce.search': 'Hledat (title, slug, klíčové slovo)…',
    'pruvodce.filter.all_categories': 'Všechny kategorie',
    'pruvodce.filter.all_statuses': 'Všechny stavy',
    'pruvodce.status.draft': 'Koncept',
    'pruvodce.status.published': 'Publikováno',
    'pruvodce.col.title': 'Článek',
    'pruvodce.col.category': 'Kategorie',
    'pruvodce.col.keyword': '🎯 Klíčové slovo',
    'pruvodce.col.status': 'Stav',
    'pruvodce.col.updated': 'Aktualizace',
    'pruvodce.tab.content': 'Obsah',
    'pruvodce.tab.seo': 'SEO',
    'pruvodce.tab.cover': 'Obálka a meta',
    'pruvodce.tab.related': 'Související',
    'pruvodce.fields.category': 'Kategorie',
    'pruvodce.fields.slug': 'Slug (URL)',
    'pruvodce.fields.title': 'Název (H1)',
    'pruvodce.fields.excerpt': 'Krátký popis (karta v katalogu)',
    'pruvodce.fields.body': 'Tělo článku',
    'pruvodce.fields.seo_keyword': 'Primární klíčové slovo',
    'pruvodce.fields.seo_keyword_help': 'Hlavní hledaný výraz, pod který je článek zacílen.',
    'pruvodce.fields.seo_secondary': 'Sekundární klíčová slova',
    'pruvodce.fields.seo_volume': 'Odhadovaný měsíční objem hledání',
    'pruvodce.fields.seo_intent': 'Intent',
    'pruvodce.fields.meta_title': 'Meta title (override)',
    'pruvodce.fields.meta_description': 'Meta description (override)',
    'pruvodce.fields.cover': 'Obálka',
    'pruvodce.fields.cover_alt': 'Alt obálky',
    'pruvodce.fields.related': 'Související články (max 8)',
    'pruvodce.actions.save': 'Uložit',
    'pruvodce.actions.saving': 'Ukládám…',
    'pruvodce.actions.publish': 'Publikovat',
    'pruvodce.actions.unpublish': 'Vrátit do konceptu',
    'pruvodce.actions.delete': 'Smazat',
    'pruvodce.actions.delete_confirm': 'Smazat článek? Akce je nevratná.',
    'pruvodce.actions.preview': 'Zobrazit na webu',
    'pruvodce.actions.back': 'Zpět na seznam',
    'pruvodce.warn.commercial': 'Pozor: ve slugu/názvu je komerční termín ({terms}). Tento článek může konkurovat /kratom/* katalogu.',
    'pruvodce.empty': 'Nic nenalezeno',
    'pruvodce.cover.upload': 'Nahrát obálku',
    'pruvodce.cover.uploading': 'Nahrávám…',
    'pruvodce.cover.from_library': 'Vybrat z knihovny',
    'pruvodce.cover.url_placeholder': 'Nebo zadejte URL obrázku',
    'pruvodce.cover.remove': 'Odebrat obálku',
    'pruvodce.cover.save_first': 'Nejdříve uložte článek, poté lze nahrát soubor obálky.',
    'pruvodce.related.search': 'Hledat článek…',
    'pruvodce.related.no_candidates': 'Žádné publikované články.',
    'pruvodce.related.limit_reached': 'Dosažen limit 8 článků.',
    'pruvodce.categories.title': 'Kategorie wiki',
    'pruvodce.categories.add': 'Nová kategorie',
    'pruvodce.categories.edit': 'Upravit kategorii',
    'pruvodce.categories.slug': 'Slug',
    'pruvodce.categories.name': 'Název',
    'pruvodce.categories.eyebrow': 'Nadpisek (eyebrow)',
    'pruvodce.categories.description': 'Popis',
    'pruvodce.categories.icon': 'Ikona',
    'pruvodce.categories.accent': 'Akcent',
    'pruvodce.categories.position': 'Pozice',
    'pruvodce.categories.is_active': 'Aktivní',
    'pruvodce.categories.articles_count': 'Články',
    'pruvodce.categories.delete_confirm': 'Smazat kategorii?',
    'pruvodce.categories.cancel': 'Zrušit',
    'pruvodce.categories.save': 'Uložit',
    'pruvodce.categories.empty': 'Žádné kategorie. Přidejte první.',
}

const messages: Record<string, Record<string, string>> = {
  ru: fallbackMessages,
}

type LocalizedValue = Record<string, string>

export function useAdminI18n() {
  const appConfig = useAppConfig()
  const adminI18n = computed(() => appConfig.adminI18n)
  const uiLocale = computed(() => adminI18n.value?.uiLocale ?? 'ru')
  const dateTimeLocale = computed(() => adminI18n.value?.dateTimeLocale ?? 'ru-RU')
  const contentLocales = computed(() => adminI18n.value?.contentLocales ?? [])
  const contentLocaleCodes = computed(() => contentLocales.value.map((locale) => locale.code))
  const primaryContentLocale = computed(() => adminI18n.value?.primaryContentLocale ?? contentLocaleCodes.value[0] ?? 'cs')
  const previewContentLocale = computed(() => {
    const configured = adminI18n.value?.previewContentLocale
    if (configured && contentLocaleCodes.value.includes(configured)) {
      return configured
    }

    return contentLocaleCodes.value.includes(uiLocale.value) ? uiLocale.value : primaryContentLocale.value
  })

  function t(key: string, params: Record<string, string | number> = {}) {
    const dictionary = messages[uiLocale.value] ?? fallbackMessages
    let text = dictionary[key] ?? fallbackMessages[key] ?? key

    for (const [param, value] of Object.entries(params)) {
      text = text.replaceAll(`:${param}`, String(value))
    }

    return text
  }

  function emptyLocalizedValue(fill = ''): LocalizedValue {
    return Object.fromEntries(contentLocaleCodes.value.map((code) => [code, fill]))
  }

  function normalizeLocalizedValue(value: unknown, fill = ''): LocalizedValue {
    const normalized = emptyLocalizedValue(fill)

    if (!value || typeof value !== 'object') {
      return normalized
    }

    for (const code of contentLocaleCodes.value) {
      const candidate = (value as Record<string, unknown>)[code]
      if (typeof candidate === 'string') {
        normalized[code] = candidate
      }
    }

    return normalized
  }

  function pickLocalizedValue(value: unknown, preferred?: string): string {
    if (typeof value === 'string') {
      return value
    }

    if (!value || typeof value !== 'object') {
      return ''
    }

    const translations = value as Record<string, unknown>
    const candidates = [preferred, previewContentLocale.value, primaryContentLocale.value, ...contentLocaleCodes.value]
      .filter((candidate, index, list): candidate is string => Boolean(candidate) && list.indexOf(candidate) === index)

    for (const code of candidates) {
      const candidate = translations[code]
      if (typeof candidate === 'string' && candidate.trim() !== '') {
        return candidate.trim()
      }
    }

    for (const candidate of Object.values(translations)) {
      if (typeof candidate === 'string' && candidate.trim() !== '') {
        return candidate.trim()
      }
    }

    return ''
  }

  function formatDateTime(value: string | number | Date | null | undefined) {
    if (!value) {
      return ''
    }

    return new Intl.DateTimeFormat(dateTimeLocale.value, {
      dateStyle: 'medium',
      timeStyle: 'short',
    }).format(new Date(value))
  }

  function statusLabel(status: string) {
    return t(`status.${status}`)
  }

  return {
    uiLocale,
    contentLocales,
    contentLocaleCodes,
    primaryContentLocale,
    previewContentLocale,
    t,
    emptyLocalizedValue,
    normalizeLocalizedValue,
    pickLocalizedValue,
    formatDateTime,
    statusLabel,
  }
}
