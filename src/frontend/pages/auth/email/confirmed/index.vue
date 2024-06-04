<script setup>
const {t} = useI18n()
const route = useRoute()

if(route.query.error) {
  throw createError({ statusCode: route.query.error_code, message: route.query.error_description })
}

onMounted(() => {

  useNoty().setNoty({
    content: t('noty.auth.email.changed.success')
  }, 7000)

  navigateTo('/')

})
</script>

<template>
  <div></div>
</template>