<script setup>
import { useFeedbackStore } from '~~/store/feedback';

const {t} = useI18n()

const breadcrumbs = [
  {
    name: t('title.home'),
    item: '/'
  },{
    name: t('title.contacts'),
    item: '/contacts'
  }
]

const feedback = ref({
  type: 'contact_page',
  name: null,
  phone: null,
  email: null,
  text: null
})

const errors = ref(null)

// COMPUTEDS
// METHODS
const resetFeedback = () => {
  feedback.value.name = null
  feedback.value.phone = null
  feedback.value.email = null
  feedback.value.text = null
}

const resetErrors = () => {
  errors.value = null
}

const createFeedbackHandler = () => {
  useFeedbackStore().create(feedback.value).then(({data, error}) => {

    if(data) {
      useNoty().setNoty({
        content: t('noty.feedback.sent'),
        type: 'success'
      }, 5000)

      resetFeedback()
      resetErrors()
    }

    if(error) {
      throw error
    }

  }).catch((e) => {

    useNoty().setNoty({
      title: t('noty.feedback.fail'),
      content: t('error.check_fields'),
      type: 'error'
    }, 5000)

    errors.value = e
  })
}
// HANDLERS
// WATCHERS

//
onServerPrefetch(() => {
  useSeo().setPageSeo(t('title.contacts'))
})
</script>

<style src='./contacts.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div class="title-common">{{ t('title.contacts') }}</div>

      <div class="contacts">
        <div class="contacts-wrapper">

          <div class="contacts-block">
            <div class="contacts-label">{{ t('phones') }}</div>
            <div class="phone">
              <a
                :href="useContacts().phone"
                class="phone-item contacts-value"
              >{{ useContacts().phone }}</a>
              <a
                :href="useContacts().phone2"
                class="phone-item contacts-value"
              >{{ useContacts().phone2 }}</a>
            </div>
          </div>

          <div class="contacts-block">
            <div class="contacts-label">{{ t('email') }}</div>
            <div class="phone">
              <a
                :href="useContacts().email"
                class="phone-item contacts-value"
              >{{ useContacts().email }}</a>
            </div>
          </div>

          <div class="contacts-block">
            <div class="contacts-label">{{ t('label.our_address') }}</div>
            <div class="phone">
              <a
                :href="useContacts().phone"
                class="phone-item contacts-value"
              >{{ useContacts().address }}</a>
            </div>
          </div>

          <div class="contacts-block">
            <div class="contacts-label">{{ t('social') }}</div>

            <div class="social">
              <a href="/" class="social-item instagram-bg">
                <IconCSS name="iconoir:instagram" class="social-icon"></IconCSS>
                <span class="social-text">Instagram</span>
              </a>
              <a href="/" class="social-item facebook-bg">
                <IconCSS name="iconoir:facebook" class="social-icon"></IconCSS>
                <span class="social-text">Facebook</span>
              </a>
              <a href="/" class="social-item tiktok-bg">
                <IconCSS name="mingcute:tiktok-line" class="social-icon"></IconCSS>
                <span class="social-text">Tiktok</span>
              </a>
            </div>
          </div>

        </div>
        
        <div class="map-wrapper">
          <iframe
            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2645.7133162726823!2d35.04091241566039!3d48.462029879250544!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40dbe2e0a9bfa945%3A0xaaf5051d68c48350!2z0YPQuy4g0KLRgNC-0LjRhtC60LDRjywgMywg0JTQvdC40L_RgNC-LCDQlNC90LXQv9GA0L7Qv9C10YLRgNC-0LLRgdC60LDRjyDQvtCx0LvQsNGB0YLRjCwgNDkwMDA!5e0!3m2!1sru!2sua!4v1605093594459!5m2!1sru!2sua"
            width="600"
            height="600"
            frameborder="0"
            allowfullscreen="allowfullscreen"
            aria-hidden="false"
            tabindex="0"
            class="map-iframe"
          ></iframe>
        </div>

        <div class="form-wrapper">
          <div class="form-label">👩‍💻 {{ t('questions') }}</div>
          
          <form-text
            v-model="feedback.name"
            :placeholder="t('form.enter.name')"
            :error="errors?.name"
            @input="() => errors.name = null"
          ></form-text>
          
          <form-text
            v-model="feedback.email"
            :placeholder="t('form.enter.email')"
            :error="errors?.email"
            @input="() => errors.email = null"
          ></form-text>
          
          <form-text
            v-model="feedback.phone"
            :placeholder="t('form.enter.phone')"
            :error="errors?.phone"
            @input="() => errors.phone = null"
          ></form-text>
          
          <form-textarea
            v-model="feedback.text"
            :placeholder="t('form.enter.message')"
            :error="errors?.text"
            @input="() => errors.text = null"
            :min-height="120"
          ></form-textarea>

          <button @click="createFeedbackHandler" class="button primary form-button">{{ t('send') }}</button>
        </div>

      </div>
    </div>
  </div>
</template>