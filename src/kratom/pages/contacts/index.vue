<script setup lang="ts">
const { t } = useI18n()
const contacts = useContacts()
const { messengers, networks } = useSocial()

const breadcrumbs = computed(() => [
  { name: t('title.home'), item: '/' },
  { name: t('title.contacts'), item: '/contacts' },
])

const feedback = ref({
  type: 'contact_page',
  name: null,
  phone: null,
  email: null,
  text: null,
})
const errors = ref<Record<string, any> | null>(null)

const submit = async () => {
  try {
    const { error } = await useFeedbackStore().create(feedback.value)
    if (error?.value) {
      throw error.value
    }

    useNoty().setNoty({
      title: t('noty.feedback.sent'),
      content: t('noty.feedback.sent'),
      type: 'success',
    }, 5000)

    feedback.value = { type: 'contact_page', name: null, phone: null, email: null, text: null }
    errors.value = null
  } catch (err: any) {
    errors.value = err?.data?.options || err || null
    useNoty().setNoty({
      title: t('noty.feedback.fail'),
      content: t('error.check_fields'),
      type: 'error',
    }, 5000)
  }
}

useSeo().setPageSeo(t('title.contacts'))
</script>

<template>
  <div class="page-base kratom-content-page">
    <div class="container kratom-content-shell">
      <the-breadcrumbs :crumbs="breadcrumbs" />
      <h1 class="title-common">{{ t('title.contacts') }}</h1>

      <div class="kratom-contacts-grid">
        <section class="kratom-content-card">
          <div class="kratom-contact-block">
            <p class="kratom-content-shell__eyebrow">{{ t('kratom.contacts.phone') }}</p>
            <a :href="`tel:${String(contacts.phone.value || '').replace(/\s+/g, '')}`">{{ contacts.phone }}</a>
          </div>
          <div class="kratom-contact-block">
            <p class="kratom-content-shell__eyebrow">{{ t('kratom.contacts.email') }}</p>
            <a :href="`mailto:${contacts.email}`">{{ contacts.email }}</a>
          </div>
          <div class="kratom-contact-block">
            <p class="kratom-content-shell__eyebrow">{{ t('kratom.contacts.address') }}</p>
            <span>{{ contacts.address }}</span>
          </div>
          <div class="kratom-contact-block">
            <p class="kratom-content-shell__eyebrow">{{ t('kratom.contacts.social') }}</p>
            <div class="kratom-contact-socials">
              <a v-for="item in [...messengers, ...networks]" :key="item.key" :href="item.link" target="_blank" rel="noopener">
                <IconCSS :name="item.icon" />
                <span>{{ item.name }}</span>
              </a>
            </div>
          </div>
          <div v-if="contacts.map" class="kratom-contact-map" v-html="contacts.map"></div>
        </section>

        <section class="kratom-content-card">
          <p class="kratom-content-shell__eyebrow">{{ t('kratom.contacts.support') }}</p>
          <div class="kratom-contact-form">
            <form-text v-model="feedback.name" :placeholder="t('form.enter.name')" :error="errors?.name" />
            <form-text v-model="feedback.email" :placeholder="t('form.enter.email')" :error="errors?.email" />
            <form-text v-model="feedback.phone" :placeholder="t('form.enter.phone')" :error="errors?.phone" />
            <form-textarea v-model="feedback.text" :placeholder="t('form.enter.message')" :error="errors?.text" :min-height="140" />
            <button type="button" class="button primary" @click="submit">{{ t('button.send') }}</button>
          </div>
        </section>
      </div>
    </div>
  </div>
</template>

<style scoped lang="scss">
.kratom-content-shell { max-width: 1120px; padding-top: 32px; }
.kratom-content-shell__eyebrow { margin-bottom: 12px; color: #8a5a2b; text-transform: uppercase; letter-spacing: .12em; font-size: 12px; font-weight: 700; }
.kratom-contacts-grid { display: grid; gap: 24px; @include desktop { grid-template-columns: minmax(0, 1fr) minmax(360px, .8fr); } }
.kratom-content-card { padding: 28px; border-radius: 32px; background: rgba(255,250,244,.92); border: 1px solid rgba(74,91,68,.1); }
.kratom-contact-block + .kratom-contact-block { margin-top: 22px; }
.kratom-contact-block a, .kratom-contact-block span { color: #233120; text-decoration: none; line-height: 1.7; }
.kratom-contact-socials { display: flex; flex-wrap: wrap; gap: 10px; }
.kratom-contact-socials a { padding: 12px 14px; border-radius: 999px; display: inline-flex; align-items: center; gap: 8px; background: rgba(247,239,231,.86); }
.kratom-contact-form { display: grid; gap: 14px; }
:deep(.kratom-contact-map iframe) { width: 100%; min-height: 280px; border: 0; border-radius: 24px; }
</style>
