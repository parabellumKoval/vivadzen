<script setup>
const {t} = useI18n()
const props = defineProps({
  source: {
    type: String,
    default: null
  },
  name: {
    type: String
  }
})
// COMPUTEDS

const link = computed(() => {
  let url = null
  let link = {
    type: null,
    href: props.source || null
  }


  if(!link.href) {
    return link;
  }

  // type = link.match('^(?:https?://)?(?:www.)?(\w*)\/')

  try {
    url = new URL(link.href)
  }catch(e) {
    console.log(e)
  }

  if(url.host.match('(?:www.)?facebook.com')){ 
    link.type = 'facebook'
  }else if(url.host.match('(?:www.)?instagram.com')) {
    link.type = 'instagram'
  }else {
    link.type = null
  }
  
  return link
})

const socialName = computed(() => {
  return link.value.type.charAt(0).toUpperCase() + link.value.type.slice(1)
})
// METHODS
// HANDLERS
// WATCHERS

console.log('props', props.name, props.source)
</script>

<style src='./social.scss' lang='scss' scoped></style>
<i18n src='./lang.yaml' lang='yaml'></i18n>

<template>
  <div v-if="link.href">
    <a
      v-if="!name"
      :href="link.href"
      :class="link.type + '-link'"
      target="_blank"
      rel="nofollow"
      class="social-link"
    >
      <IconCSS :name="'basil:' + link.type +'-outline'" class="social-link-icon"></IconCSS>
      <span class="social-link-text">{{ socialName }} {{ t('author') }}</span>
    </a>
    <a v-else
      :href="link.href"
      target="_blank"
      rel="nofollow"
      class="name-link"
    >
      <IconCSS :name="'basil:' + link.type +'-outline'" class="name-link-icon"></IconCSS>
      <span class="name-link-text">{{ name }}</span>
    </a>
  </div>
</template>