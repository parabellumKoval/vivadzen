const fallbackMessages: Record<string, string> = {
    'nav.dashboard': 'Панель',
    'nav.products': 'Товары',
    'nav.taxonomies': 'Таксономии',
    'nav.orders': 'Заказы',
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
    'status.pending': 'Новый',
    'status.received': 'Принят',
    'status.paid': 'Оплачен',
    'status.packed': 'Упакован',
    'status.shipped': 'Отправлен',
    'status.delivered': 'Доставлен',
    'status.cancelled': 'Отменён',
    'status.refunded': 'Возврат',
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
