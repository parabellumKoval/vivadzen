<script setup>
const {t} = useI18n()

const socials = computed(() => {
  return useSocial().networks
})
</script>

<style src="./contacts.scss" lang="scss" scoped />
<!-- <i18n src="./lang.yaml" lang="yaml"></i18n> -->

<template>
  <modal-wrapper :title="t('title.contacts')">
    <div class="contacts">
      <div>
        <div class="phones">
          <a href="/" class="button secondary contacts-btn">
            <IconCSS name="iconoir:phone" class="icon"></IconCSS>
            <span class="text">{{ useContacts().phone }}</span>
          </a>
          <a href="/" class="button secondary contacts-btn">
            <IconCSS name="iconoir:phone" class="icon"></IconCSS>
            <span class="text">{{ useContacts().phone2 }}</span>
          </a>
          <a href="/" class="button secondary contacts-btn email-btn">
            <IconCSS name="iconoir:mail-opened" class="icon"></IconCSS>
            <span class="text">{{ useContacts().email }}</span>
          </a>
        </div>
        <div class="social">
          <a v-for="social in socials" :key="social.id" :href="social.link" :class="social.key + '-bg'" class="button social-btn contacts-btn">
            <IconCSS :name="social.icon" class="icon"></IconCSS>
            <span class="text">{{ social.name }}</span>
          </a>
        </div>
      </div>
      <div class="divide"></div>
      <div class="map">
        <div class="map-title">{{ useContacts().address }}</div>
        <iframe class="map-iframe" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2645.7035509988445!2d35.04097347643741!3d48.46221722850475!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x40dbe2de002738ef%3A0x5f65922ec1cc7df3!2z0YPQuy4g0KLRgNC-0LjRhtC60LDRjywgM9CwLCDQlNC90LXQv9GALCDQlNC90LXQv9GA0L7Qv9C10YLRgNC-0LLRgdC60LDRjyDQvtCx0LvQsNGB0YLRjCwgNDkwMDA!5e0!3m2!1sru!2sua!4v1702042564258!5m2!1sru!2sua" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
      </div>
    </div>
  </modal-wrapper>
</template>