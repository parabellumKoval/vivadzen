<script setup lang="ts">
const props = defineProps<{
  modelValue: string | null
  required: boolean
  publicKey: string
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string | null): void
  (e: 'error', value: string): void
}>()

const { t } = useI18n()
const runtimeConfig = useRuntimeConfig()
const adultoGuideModal = defineAsyncComponent(() => import('~/components/Modal/AdultoGuide/AdultoGuide.vue'))

const containerId = `adulto-verification-${Math.random().toString(36).slice(2, 10)}`
const containerRef = ref<HTMLElement | null>(null)
const isLoading = ref(false)
const widgetError = ref<string | null>(null)
const scriptId = 'adulto-widget-script'
const scriptLoadTimeoutMs = 15000
const fallbackRenderTimeoutMs = 5000
const containerWaitTimeoutMs = 3000
const isMounted = ref(false)

let adultoScriptPromise: Promise<void> | null = null
let uidObserver: MutationObserver | null = null
let initRunId = 0
const publicKeyForDom = computed(() => String(props.publicKey || '').trim())

const getAdultoApi = () => (window as typeof window & { Adulto?: any }).Adulto
const isAdultoApiReady = () => {
  const adultoApi = getAdultoApi()
  return Boolean(adultoApi && typeof adultoApi.createVerificationWidget === 'function')
}
const getKeyPreview = (key: string) => {
  if (!key) {
    return ''
  }

  if (key.length <= 4) {
    return key
  }

  return `${key.slice(0, 4)}...`
}

const openAdultoGuide = () => {
  useModal().open(adultoGuideModal, null, null, {
    width: { min: 320, max: 920 },
    height: { min: 520, max: 860 },
  })
}

const getScriptUrlTemplate = () => {
  return String(runtimeConfig.public.adultoWidgetScriptUrl || 'https://api.js.m2a.cz/api.js').trim()
}

const buildScriptSrc = (publicKey: string) => {
  const urlTemplate = getScriptUrlTemplate()

  if (urlTemplate.includes('{publicKey}')) {
    return urlTemplate.replaceAll('{publicKey}', encodeURIComponent(publicKey))
  }

  if (urlTemplate.includes('{key}')) {
    return urlTemplate.replaceAll('{key}', encodeURIComponent(publicKey))
  }

  return urlTemplate
}

const logAdultoDebug = (stage: string, error?: unknown, extra: Record<string, unknown> = {}) => {
  if (!process.client) {
    return
  }

  const script = document.getElementById(scriptId) as HTMLScriptElement | null
  const errorMessage = error instanceof Error ? error.message : String(error || '')
  const key = String(props.publicKey || '').trim()
  const payload = {
    stage,
    error: errorMessage,
    href: window.location.href,
    host: window.location.host,
    isLocalhost: ['localhost', '127.0.0.1'].includes(window.location.hostname),
    publicKeyProvided: key.length > 0,
    publicKeyPreview: getKeyPreview(key),
    scriptUrlTemplate: getScriptUrlTemplate(),
    scriptSrc: script?.src || null,
    scriptState: script?.dataset?.adultoState || null,
    hasAdultoObject: Boolean(getAdultoApi()),
    adultoApiReady: isAdultoApiReady(),
    ...extra,
  }

  const isFailureStage = /(failed|error|timeout)/i.test(stage)

  if (isFailureStage || errorMessage) {
    console.error('[ADULTO] Widget debug', payload)
    return
  }

  console.info('[ADULTO] Widget debug', payload)
}

const cleanupUidObserver = () => {
  if (!uidObserver) {
    return
  }

  uidObserver.disconnect()
  uidObserver = null
}

const syncUidFromDom = () => {
  const selectors = [
    'input[name="adultocz-uid"]',
    'input[name="adultocz_verify_uid"]',
    'input[name="age_verification_uid"]',
  ]

  for (const selector of selectors) {
    const input = document.querySelector(selector) as HTMLInputElement | null
    const value = String(input?.value || '').trim()

    if (value) {
      if (value !== String(props.modelValue || '').trim()) {
        emit('update:modelValue', value)
      }
      widgetError.value = null
      return
    }
  }
}

const getTargetElement = () => {
  return (containerRef.value || document.getElementById(containerId)) as HTMLElement | null
}

const waitForContainerElement = async () => {
  const startedAt = Date.now()

  while (Date.now() - startedAt < containerWaitTimeoutMs) {
    const targetElement = getTargetElement()
    if (targetElement) {
      return targetElement
    }

    await nextTick()
    await new Promise((resolve) => window.setTimeout(resolve, 50))
  }

  return getTargetElement()
}

const ensureFallbackMarkup = (targetElement: HTMLElement, publicKey: string) => {
  let widgetRoot = targetElement.querySelector('.adulto-cz') as HTMLElement | null

  if (!widgetRoot) {
    widgetRoot = document.createElement('div')
    widgetRoot.className = 'adulto-cz'
    targetElement.appendChild(widgetRoot)
  }

  widgetRoot.setAttribute('data-sitekey', publicKey)
  return widgetRoot
}

const hasUidInDom = () => {
  const selectors = [
    'input[name="adultocz-uid"]',
    'input[name="adultocz_verify_uid"]',
    'input[name="age_verification_uid"]',
  ]

  return selectors.some((selector) => {
    const input = document.querySelector(selector) as HTMLInputElement | null
    return String(input?.value || '').trim().length > 0
  })
}

const hasRenderedWidgetInContainer = () => {
  const targetElement = getTargetElement()
  if (!targetElement) {
    return false
  }

  if (targetElement.querySelector('iframe')) {
    return true
  }

  const widgetRoot = targetElement.querySelector('.adulto-cz') as HTMLElement | null
  if (!widgetRoot) {
    return false
  }

  return widgetRoot.childElementCount > 0
}

const waitForFallbackRender = async () => {
  const startedAt = Date.now()

  while (Date.now() - startedAt < fallbackRenderTimeoutMs) {
    if (hasRenderedWidgetInContainer() || hasUidInDom()) {
      return true
    }

    await new Promise((resolve) => window.setTimeout(resolve, 200))
  }

  return hasRenderedWidgetInContainer() || hasUidInDom()
}

const mountDomWidgetFallback = async (targetElement: HTMLElement, publicKey: string) => {
  cleanupUidObserver()
  ensureFallbackMarkup(targetElement, publicKey)

  await nextTick()
  syncUidFromDom()

  uidObserver = new MutationObserver(() => {
    syncUidFromDom()
  })
  uidObserver.observe(document.body, {
    childList: true,
    subtree: true,
    attributes: true,
    attributeFilter: ['value'],
  })

  logAdultoDebug('dom_widget_fallback_mounted')
}

const loadScript = async (publicKey: string) => {
  if (!process.client) {
    return
  }

  if (isAdultoApiReady()) {
    return
  }

  if (adultoScriptPromise) {
    return adultoScriptPromise
  }

  adultoScriptPromise = new Promise<void>((resolve, reject) => {
    const scriptSrc = buildScriptSrc(publicKey)
    const existing = document.getElementById(scriptId) as HTMLScriptElement | null
    let settled = false

    const finish = (callback: () => void) => {
      if (settled) {
        return
      }

      settled = true
      callback()
    }

    const resolveWhenReady = () => {
      finish(() => resolve())
    }

    const rejectWith = (message: string, stage: string, extra: Record<string, unknown> = {}) => {
      logAdultoDebug(stage, message, extra)
      finish(() => reject(new Error(message)))
    }

    const timeoutId = window.setTimeout(() => {
      rejectWith('Timed out while loading ADULTO script.', 'script_timeout', { timeoutMs: scriptLoadTimeoutMs })
    }, scriptLoadTimeoutMs)

    const clearTimeoutIfSettled = () => {
      if (settled) {
        window.clearTimeout(timeoutId)
      }
    }

    if (existing) {
      const existingState = existing.dataset.adultoState

      if (existing.src && existing.src !== scriptSrc) {
        existing.remove()
        logAdultoDebug('existing_script_replaced', '', { previousSrc: existing.src, nextSrc: scriptSrc })
      } else {
        if (existingState === 'loaded') {
          logAdultoDebug('script_reused_loaded', '', { scriptSrc })
          resolveWhenReady()
          clearTimeoutIfSettled()
          return
        }

        if (existingState === 'error') {
          rejectWith('Failed to load ADULTO script.', 'existing_script_state_error')
          clearTimeoutIfSettled()
          return
        }

        existing.addEventListener(
          'load',
          () => {
            existing.dataset.adultoState = 'loaded'
            logAdultoDebug('existing_script_loaded', '', { scriptSrc })
            resolveWhenReady()
            clearTimeoutIfSettled()
          },
          { once: true }
        )
        existing.addEventListener(
          'error',
          () => {
            existing.dataset.adultoState = 'error'
            rejectWith('Failed to load ADULTO script.', 'existing_script_onerror')
            clearTimeoutIfSettled()
          },
          { once: true }
        )
        return
      }
    }

    const script = document.createElement('script')
    script.id = scriptId
    script.async = true
    script.dataset.adultoState = 'loading'
    script.src = scriptSrc
    script.onload = () => {
      script.dataset.adultoState = 'loaded'
      logAdultoDebug('script_loaded', '', { scriptSrc })
      resolveWhenReady()
      clearTimeoutIfSettled()
    }
    script.onerror = () => {
      script.dataset.adultoState = 'error'
      rejectWith('Failed to load ADULTO script.', 'script_onerror')
      clearTimeoutIfSettled()
    }

    document.head.appendChild(script)
  }).catch((error) => {
    adultoScriptPromise = null
    throw error
  })

  return adultoScriptPromise
}

const initWidget = async () => {
  const runId = ++initRunId
  const isActiveRun = () => isMounted.value && runId === initRunId

  widgetError.value = null

  if (!process.client || !props.required || !isMounted.value) {
    return
  }

  const publicKey = publicKeyForDom.value
  if (!publicKey) {
    widgetError.value = t('unavailable')
    logAdultoDebug('public_key_missing')
    return
  }

  isLoading.value = true

  try {
    const targetElement = await waitForContainerElement()
    if (!targetElement) {
      const details = `ADULTO container was not found in ${containerWaitTimeoutMs}ms.`
      widgetError.value = t('widget_load_failed')
      logAdultoDebug('container_missing_skip', details, { containerId })
      emit('error', `${widgetError.value} (${details})`)
      return
    }

    logAdultoDebug('container_ready', '', {
      containerId,
      hasFallbackDiv: Boolean(targetElement.querySelector('.adulto-cz')),
    })

    await mountDomWidgetFallback(targetElement, publicKey)

    const existingScript = document.getElementById(scriptId) as HTMLScriptElement | null
    if (existingScript?.dataset?.adultoState === 'loaded' && !isAdultoApiReady()) {
      existingScript.remove()
      adultoScriptPromise = null
      logAdultoDebug('reload_script_for_fallback', '', { containerId })
    }

    await loadScript(publicKey)

    await nextTick()

    if (!isActiveRun()) {
      return
    }

    const adultoApi = getAdultoApi()
    if (adultoApi && typeof adultoApi.createVerificationWidget === 'function') {
      targetElement.innerHTML = ''
      adultoApi.createVerificationWidget({
        elementId: containerId,
        publicKey,
        siteKey: publicKey,
        onSuccess: (uid: string) => {
          if (!isActiveRun()) {
            return
          }
          emit('update:modelValue', uid)
          widgetError.value = null
        },
        onError: (error: unknown) => {
          if (!isActiveRun()) {
            return
          }
          const message = t('verification_failed')
          widgetError.value = message
          logAdultoDebug('widget_onerror_callback', error)
          emit('error', message + (error ? ` (${String(error)})` : ''))
        },
      })
    } else {
      const hasRendered = await waitForFallbackRender()

      if (!hasRendered && !String(props.modelValue || '').trim()) {
        const details = 'ADULTO fallback did not render widget in container. Check form placement and domain/key mapping.'
        widgetError.value = t('widget_load_failed')
        logAdultoDebug('fallback_widget_not_rendered', details, { containerId })
        emit('error', `${widgetError.value} (${details})`)
      } else {
        logAdultoDebug('fallback_widget_rendered_or_uid_found', '', { containerId })
      }
    }
  } catch (error) {
    if (!isActiveRun()) {
      return
    }
    widgetError.value = t('widget_load_failed')
    logAdultoDebug('init_widget_failed', error, { containerId })
    const details = error instanceof Error ? error.message : String(error)
    emit('error', `${widgetError.value} (${details})`)
  } finally {
    if (isActiveRun()) {
      isLoading.value = false
    }
  }
}

watch(
  () => [props.required, props.publicKey],
  () => {
    if (!props.required) {
      widgetError.value = null
      cleanupUidObserver()
      return
    }

    if (!isMounted.value) {
      return
    }

    initWidget()
  }
)

onMounted(() => {
  isMounted.value = true
  if (props.required) {
    initWidget()
  }
})

onBeforeUnmount(() => {
  isMounted.value = false
  initRunId++
  cleanupUidObserver()
})
</script>

<style scoped lang="scss">
.adulto-verification {
  display: grid;
  gap: 10px;
}

.adulto-verification__title {
  font-size: 15px;
  font-weight: 600;
}

.adulto-verification__description {
  font-size: 13px;
  color: $color-3;
}

.adulto-verification__guide {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 14px;
  border: 1px solid #e7e0d2;
  border-radius: 12px;
  background: #f8f6f1;
}

.adulto-verification__guide-text {
  margin: 0;
  font-size: 13px;
  color: #645a4f;
}

.adulto-verification__guide-button {
  padding: 0;
  border: 0;
  background: transparent;
  color: #8a5130;
  font-size: 13px;
  font-weight: 600;
  text-decoration: underline;
  text-underline-offset: 2px;
  cursor: pointer;
}

.adulto-verification__form {
  margin: 0;
}

.adulto-verification__form :deep(.adulto-cz > div:not([class])) {
  z-index: 1 !important;
}

.adulto-verification__status {
  font-size: 13px;
}

.adulto-verification__status--success {
  color: #278248;
}

.adulto-verification__status--error {
  color: #b53c3c;
}
</style>

<i18n lang="yaml">
uk:
  title: "Підтвердження віку 18+"
  description: "Для оформлення цієї корзини в регіоні CZ потрібно пройти перевірку віку через ADULTO.cz."
  guide_hint: "Ось інструкція з підтвердження віку, якщо потрібна допомога."
  guide_action: "Відкрити інструкцію"
  verified: "Вік підтверджено."
  loading: "Завантаження віджета..."
  unavailable: "Публічний ключ ADULTO не налаштований."
  verification_failed: "Перевірка віку не пройдена."
  widget_load_failed: "Не вдалося завантажити віджет ADULTO."
ru:
  title: "Подтверждение возраста 18+"
  description: "Для оформления этой корзины в регионе CZ нужно пройти проверку возраста через ADULTO.cz."
  guide_hint: "Вот инструкция по подтверждению возраста, если нужна помощь."
  guide_action: "Открыть инструкцию"
  verified: "Возраст подтверждён."
  loading: "Загрузка виджета..."
  unavailable: "Публичный ключ ADULTO не настроен."
  verification_failed: "Проверка возраста не пройдена."
  widget_load_failed: "Не удалось загрузить виджет ADULTO."
cs:
  title: "Ověření věku 18+"
  description: "Pro dokončení tohoto košíku v regionu CZ je nutné ověření věku přes ADULTO.cz."
  guide_hint: "Zde je návod k ověření věku, pokud potřebujete pomoc."
  guide_action: "Otevřít návod"
  verified: "Věk byl ověřen."
  loading: "Načítání widgetu..."
  unavailable: "Veřejný klíč ADULTO není nastaven."
  verification_failed: "Ověření věku se nezdařilo."
  widget_load_failed: "Nepodařilo se načíst widget ADULTO."
en:
  title: "Age Verification 18+"
  description: "To place this order in the CZ region, complete age verification via ADULTO.cz."
  guide_hint: "Here is the age verification guide if you need help."
  guide_action: "Open guide"
  verified: "Age verified."
  loading: "Loading widget..."
  unavailable: "ADULTO public key is not configured."
  verification_failed: "Age verification failed."
  widget_load_failed: "Failed to load ADULTO widget."
</i18n>

<template>
  <div class="adulto-verification">
    <div class="adulto-verification__title">{{ t('title') }}</div>
    <p class="adulto-verification__description">{{ t('description') }}</p>
    <div class="adulto-verification__guide">
      <p class="adulto-verification__guide-text">{{ t('guide_hint') }}</p>
      <button type="button" class="adulto-verification__guide-button" @click="openAdultoGuide">
        {{ t('guide_action') }}
      </button>
    </div>

    <form class="adulto-verification__form" @submit.prevent>
      <div :id="containerId" ref="containerRef">
        <div class="adulto-cz" :data-sitekey="publicKeyForDom"></div>
      </div>
    </form>

    <div v-if="isLoading" class="adulto-verification__status">
      {{ t('loading') }}
    </div>

    <div v-if="modelValue" class="adulto-verification__status adulto-verification__status--success">
      {{ t('verified') }}
    </div>

    <div v-if="widgetError" class="adulto-verification__status adulto-verification__status--error">
      {{ widgetError }}
    </div>
  </div>
</template>
