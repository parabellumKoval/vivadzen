<script setup>
const {t} = useI18n()

const props = defineProps({
  description: {
    type: String,
  }
})

const url = computed(() => {
  return useRuntimeConfig().public.frontendUrl + useRoute().fullPath
})

const facebookLink = computed(() => {
  return `https://www.facebook.com/sharer/sharer.php?u=${url.value}`
})

const xLink = computed(() => {
  return `https://twitter.com/intent/tweet?text=${props.description}&url=${url.value}`
})

const copyHandler = () => {
  navigator.clipboard.writeText(url.value).then(function() {
    useNoty().setNoty({
      content: t('noty.copied', {text: url.value})
    })
  }, function(err) {
    useNoty().setNoty({
      content: t('noty.copied_fail')
    })
  })
}
</script>

<style src="./share.scss" lang="scss" scoped />
<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <div class="share">
    <div class="title">{{ t('share') }}</div>
    <div class="icons">
      <a :href="facebookLink" target="_blank" class="btn facebook-bg">
        <IconCSS name="ri:facebook-fill" size="20"></IconCSS>
      </a>
      <a :href="xLink" target="_blank" class="btn x-bg">
        <IconCSS name="ri:twitter-x-fill" size="20"></IconCSS>
      </a>
      <button @click="copyHandler" class="btn copy-bg" type="button">
        <IconCSS name="ph:link" size="20"></IconCSS>
      </button>
    </div>
  </div>
</template>