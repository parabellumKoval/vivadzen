<script setup>
const { t } = useI18n()
const { messengers, networks } = useSocial()
const hasSocialLinks = computed(() => messengers.value.length > 0 || networks.value.length > 0)
const isModalOpen = ref(false)
const isWinkActive = ref(false)

let previousBodyOverflow = ''
let winkTimeout = 0

const triggerWink = () => {
  if (winkTimeout) {
    window.clearTimeout(winkTimeout)
  }

  isWinkActive.value = false

  requestAnimationFrame(() => {
    isWinkActive.value = true
  })

  winkTimeout = window.setTimeout(() => {
    isWinkActive.value = false
    winkTimeout = 0
  }, 620)
}

const openModal = () => {
  triggerWink()
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
}

const onEscapePress = (event) => {
  if (event.key === 'Escape' && isModalOpen.value) {
    closeModal()
  }
}

watch(isModalOpen, (isOpen) => {
  if (!process.client) {
    return
  }

  if (isOpen) {
    previousBodyOverflow = document.body.style.overflow
    document.body.style.overflow = 'hidden'
  } else {
    document.body.style.overflow = previousBodyOverflow
  }
})

onMounted(() => {
  window.addEventListener('keydown', onEscapePress)
})

onBeforeUnmount(() => {
  window.removeEventListener('keydown', onEscapePress)
  document.body.style.overflow = previousBodyOverflow

  if (winkTimeout) {
    window.clearTimeout(winkTimeout)
  }
})
</script>

<i18n lang="yaml">
ru:
  button_aria: "Открыть онлайн-чат"
  modal:
    title: "Онлайн-консультация"
    desc: "Вы можете проконсультироваться на любую тему онлайн, связавшись с оператором."
    messengers: "Мессенджеры"
    social_networks: "Соц. сети"
    empty: "Ссылки временно недоступны."

uk:
  button_aria: "Відкрити онлайн-чат"
  modal:
    title: "Онлайн-консультація"
    desc: "Ви можете проконсультуватися на будь-яку тему онлайн, зв’язавшись з оператором."
    messengers: "Месенджери"
    social_networks: "Соцмережі"
    empty: "Посилання тимчасово недоступні."

en:
  button_aria: "Open online chat"
  modal:
    title: "Online consultation"
    desc: "You can get advice online on any topic by contacting an operator."
    messengers: "Messengers"
    social_networks: "Social networks"
    empty: "Links are temporarily unavailable."

cs:
  button_aria: "Otevřít online chat"
  modal:
    title: "Online konzultace"
    desc: "Můžete se online poradit na jakékoli téma po kontaktování operátora."
    messengers: "Messengery"
    social_networks: "Sociální sítě"
    empty: "Odkazy jsou dočasně nedostupné."
</i18n>

<template>
  <button
    class="chat-support-button"
    :aria-label="t('button_aria')"
    type="button"
    @click="openModal"
  >
    <span class="chat-support-button__icon-wrap">
      <IconCSS name="ph:chat-circle-dots-fill" class="chat-support-button__icon" />
      <span class="chat-support-button__eye chat-support-button__eye--left"></span>
      <span
        class="chat-support-button__eye chat-support-button__eye--right"
        :class="{ 'chat-support-button__eye--winking': isWinkActive }"
      ></span>
    </span>
  </button>

  <transition name="chat-support-modal-transition">
    <div
      v-if="isModalOpen"
      class="chat-support-modal"
      role="dialog"
      :aria-label="t('modal.title')"
      aria-modal="true"
      @click.self="closeModal"
    >
      <div class="chat-support-modal__card">
        <button class="chat-support-modal__close" type="button" @click="closeModal">
          <IconCSS name="ph:x-bold" />
        </button>

        <div class="chat-support-modal__title">{{ t('modal.title') }}</div>
        <p class="chat-support-modal__desc">{{ t('modal.desc') }}</p>

        <div v-if="hasSocialLinks" class="chat-support-modal__social-groups">
          <div v-if="messengers.length" class="chat-support-modal__social-group">
            <div class="chat-support-modal__social-group-title">{{ t('modal.messengers') }}</div>
            <div class="chat-support-modal__socials">
              <a
                v-for="messenger in messengers"
                :key="messenger.id"
                :href="messenger.link"
                class="chat-support-modal__social-link"
                :class="'chat-support-modal__social-link--' + messenger.key"
                target="_blank"
                rel="noopener noreferrer"
              >
                <IconCSS :name="messenger.icon" class="chat-support-modal__social-icon" />
                <span>{{ messenger.name }}</span>
              </a>
            </div>
          </div>

          <div v-if="networks.length" class="chat-support-modal__social-group">
            <div class="chat-support-modal__social-group-title">{{ t('modal.social_networks') }}</div>
            <div class="chat-support-modal__socials">
              <a
                v-for="network in networks"
                :key="network.id"
                :href="network.link"
                class="chat-support-modal__social-link"
                :class="'chat-support-modal__social-link--' + network.key"
                target="_blank"
                rel="noopener noreferrer"
              >
                <IconCSS :name="network.icon" class="chat-support-modal__social-icon" />
                <span>{{ network.name }}</span>
              </a>
            </div>
          </div>
        </div>

        <div v-else class="chat-support-modal__empty">
          {{ t('modal.empty') }}
        </div>
      </div>
    </div>
  </transition>
</template>

<style scoped lang="scss">
.chat-support-button {
  width: clamp(52px, 5.2vw, 62px);
  height: clamp(52px, 5.2vw, 62px);
  border: 0;
  border-radius: 50%;
  cursor: pointer;
  color: #fff;
  background:
    radial-gradient(circle at 24% 18%, #ffdaba 0%, #ff9551 42%, #ff6a1a 72%, #cc4500 100%);
  box-shadow:
    0 18px 30px rgba(204, 69, 0, 0.34),
    0 4px 10px rgba(0, 0, 0, 0.22);
  transition:
    transform 0.24s ease,
    box-shadow 0.24s ease;
}

.chat-support-button:hover {
  transform: translateY(-2px) scale(1.04);
  box-shadow:
    0 22px 34px rgba(204, 69, 0, 0.45),
    0 6px 14px rgba(0, 0, 0, 0.25);
}

.chat-support-button__icon-wrap {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  height: 100%;
  position: relative;
}

.chat-support-button__icon {
  font-size: clamp(30px, 3vw, 34px);
  filter: drop-shadow(0 2px 5px rgba(0, 0, 0, 0.22));
}

.chat-support-button__eye {
  width: clamp(5px, 0.45vw, 7px);
  height: clamp(5px, 0.45vw, 7px);
  border-radius: 50%;
  position: absolute;
  top: 34%;
  background: #1a1715;
  animation: chat-eye-blink 5.2s infinite;
  transform-origin: center;
}

.chat-support-button__eye--left {
  left: 36%;
}

.chat-support-button__eye--right {
  right: 36%;
  animation-delay: 0.9s;
}

.chat-support-button__eye--winking {
  animation: chat-eye-wink 0.62s ease;
}

.chat-support-modal {
  position: fixed;
  inset: 0;
  z-index: 210;
  background: rgba(4, 8, 14, 0.6);
  backdrop-filter: blur(6px);
  display: flex;
  justify-content: flex-end;
  align-items: flex-end;
  padding: 16px;
}

.chat-support-modal__card {
  width: min(390px, calc(100vw - 20px));
  border-radius: 22px;
  padding: 22px 20px 18px;
  background:
    linear-gradient(162deg, rgba(255, 255, 255, 0.96) 0%, rgba(255, 248, 242, 0.97) 100%);
  border: 1px solid rgba(255, 132, 49, 0.25);
  box-shadow:
    0 28px 46px rgba(0, 0, 0, 0.25),
    0 4px 16px rgba(204, 69, 0, 0.14);
  position: relative;
}

.chat-support-modal__close {
  position: absolute;
  top: 10px;
  right: 10px;
  width: 30px;
  height: 30px;
  border: 0;
  border-radius: 50%;
  background: rgba(0, 0, 0, 0.08);
  color: #2a2a2a;
  cursor: pointer;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.chat-support-modal__title {
  font-size: 1.1rem;
  font-weight: 700;
  color: #181818;
  margin-bottom: 8px;
  padding-right: 28px;
}

.chat-support-modal__desc {
  margin: 0 0 14px;
  line-height: 1.45;
  color: rgba(24, 24, 24, 0.76);
}

.chat-support-modal__socials {
  display: grid;
  grid-template-columns: 1fr;
  gap: 8px;
}

.chat-support-modal__social-groups {
  display: grid;
  gap: 12px;
}

.chat-support-modal__social-group-title {
  margin-bottom: 6px;
  font-size: 0.75rem;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
  color: rgba(24, 24, 24, 0.55);
}

.chat-support-modal__social-link {
  border-radius: 12px;
  padding: 15px 10px;
  text-decoration: none;
  font-weight: 600;
  color: #fff;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 7px;
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.chat-support-modal__social-link:hover {
  transform: translateY(-1px);
  box-shadow: 0 10px 14px rgba(0, 0, 0, 0.16);
}

.chat-support-modal__social-icon {
  font-size: 1.1rem;
}

.chat-support-modal__social-link--telegram {
  background: linear-gradient(135deg, #0088cc 0%, #0071af 100%);
}

.chat-support-modal__social-link--viber {
  background: linear-gradient(135deg, #7f3d9d 0%, #643089 100%);
}

.chat-support-modal__social-link--whatsapp {
  background: linear-gradient(135deg, #27d366 0%, #1fb153 100%);
}

.chat-support-modal__social-link--instagram {
  background: linear-gradient(135deg, #f58529 0%, #dd2a7b 100%);
}

.chat-support-modal__social-link--facebook {
  background: linear-gradient(135deg, #1877f2 0%, #0b57ae 100%);
}

.chat-support-modal__social-link--youtube {
  background: linear-gradient(135deg, #ff0000 0%, #c20000 100%);
}

.chat-support-modal__empty {
  color: rgba(24, 24, 24, 0.68);
  font-size: 0.95rem;
}

.chat-support-modal-transition-enter-active,
.chat-support-modal-transition-leave-active {
  transition: opacity 0.24s ease;
}

.chat-support-modal-transition-enter-from,
.chat-support-modal-transition-leave-to {
  opacity: 0;
}

@keyframes chat-eye-blink {
  0%, 44%, 47%, 100% {
    transform: scaleY(1);
  }

  45%, 46% {
    transform: scaleY(0.15);
  }
}

@keyframes chat-eye-wink {
  0%, 100% {
    transform: scaleY(1);
  }

  34%, 66% {
    transform: scaleY(0.12);
  }
}

@media (max-width: 767px) {
  .chat-support-button:hover {
    transform: translateY(0) scale(1);
  }

  .chat-support-modal {
    justify-content: center;
    padding: 10px;
  }

  .chat-support-modal__card {
    width: min(390px, calc(100vw - 8px));
    border-radius: 18px;
    padding: 18px 14px 14px;
  }
}
</style>
