<script setup>
const { t } = useI18n()
const modal = useModal()
const contacts = useContacts()

const color = computed(() => {
  return modal.active?.data || {}
})

const modalTitle = computed(() => {
  return color.value?.name ? `${color.value.name} ${t('title_suffix')}` : 'Kratom'
})

const contactItems = computed(() => {
  return [
    {
      icon: 'iconoir:map',
      label: t('contacts.address'),
      value: contacts.address.value
    },
    {
      icon: 'iconoir:phone',
      label: t('contacts.phone'),
      value: contacts.phone.value
    },
    {
      icon: 'iconoir:clock',
      label: t('contacts.schedule'),
      value: contacts.schedule.value
    }
  ].filter((item) => item.value)
})

const { messengers } = useSocial()

const mapRaw = computed(() => {
  const raw = contacts.map.value
  if (raw && typeof raw === 'object' && 'value' in raw) {
    return raw.value
  }
  return raw || ''
})

const mapSrc = computed(() => {
  if (!mapRaw.value || typeof mapRaw.value !== 'string') {
    return ''
  }

  const match = mapRaw.value.match(/src=["']([^"']+)["']/i)
  if (match && match[1]) {
    return match[1]
  }

  return mapRaw.value.trim()
})
</script>

<style src="./kratom-variety.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="modalTitle">
    <div class="kratom-variety-modal">
      <div class="kratom-variety-modal__hero">
        <div class="kratom-variety-modal__image">
          <nuxt-img
            :src="color.bg"
            :alt="`${color.name} ${t('title_suffix')}`"
            class="kratom-variety-modal__image-inner"
          />
        </div>
        <div class="kratom-variety-modal__content">
          <p class="kratom-variety-modal__name">
            <span class="big">{{ color.name }}</span>
            <span class="same" :style="{ color: color.color }"> {{ t('title_suffix') }}</span>
          </p>
          <div class="kratom-variety-modal__title">{{ color.title }}</div>
          <div class="kratom-variety-modal__desc">{{ color.desc }}</div>
        </div>
      </div>

      <div class="kratom-variety-modal__section">
        <div class="kratom-variety-modal__section-title">{{ t('properties') }}</div>
        <ul class="effects-list">
          <li v-for="effect in color.effects || []" :key="effect.label" class="effect-item">
            <IconCSS :name="effect.icon" class="effect-item__icon" :style="{ color: color.color }" />
            <div>
              <strong class="effect-label">{{ effect.label }}</strong>
              <div class="effect-desc">{{ effect.text }}</div>
            </div>
          </li>
        </ul>
      </div>

      <div class="kratom-variety-modal__section">
        <div class="kratom-variety-modal__section-title">{{ t('modifications') }}</div>
        <div class="kratom-variety-modal__mods">
          <div
            v-for="modification in color.modifications || []"
            :key="modification.weight"
            class="kratom-variety-modal__mod-item"
          >
            <span class="kratom-variety-modal__mod-weight">{{ modification.weight }}</span>
            <span class="kratom-variety-modal__mod-price">{{ modification.price }}</span>
          </div>
        </div>
      </div>

      <div class="kratom-variety-modal__message">
        {{ t('store_message') }}
      </div>

      <div class="kratom-variety-modal__contacts">
        <div class="kratom-variety-modal__contact-info">
          <div class="kratom-variety-modal__contact-list">
            <div
              v-for="item in contactItems"
              :key="item.label"
              class="kratom-variety-modal__contact-item"
            >
              <div class="kratom-variety-modal__contact-icon">
                <IconCSS :name="item.icon" />
              </div>
              <div class="kratom-variety-modal__contact-body">
                <div class="kratom-variety-modal__contact-label">{{ item.label }}</div>
                <div class="kratom-variety-modal__contact-value">{{ item.value }}</div>
              </div>
            </div>
          </div>

          <div v-if="messengers.length" class="kratom-variety-modal__messengers">
            <div class="kratom-variety-modal__messengers-title">{{ t('messengers') }}</div>
            <div class="kratom-variety-modal__messengers-list">
              <a
                v-for="messenger in messengers"
                :key="messenger.id"
                :href="messenger.link"
                target="_blank"
                rel="noopener noreferrer"
                class="kratom-variety-modal__messenger"
              >
                <IconCSS :name="messenger.icon" class="kratom-variety-modal__messenger-icon" />
                <span>{{ messenger.name }}</span>
              </a>
            </div>
          </div>
        </div>

        <a
          v-if="mapSrc"
          :href="mapSrc"
          target="_blank"
          rel="noopener noreferrer"
          class="kratom-variety-modal__map"
        >
          <iframe
            class="kratom-variety-modal__map-iframe"
            :src="mapSrc"
            loading="lazy"
            referrerpolicy="no-referrer-when-downgrade"
          ></iframe>
          <span class="kratom-variety-modal__map-overlay">{{ t('open_map') }}</span>
        </a>
      </div>
    </div>
  </modal-wrapper>
</template>
