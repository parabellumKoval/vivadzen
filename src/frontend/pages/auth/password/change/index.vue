<script setup>
const {t} = useI18n()
const route = useRoute()

if(route.query.error) {
  throw createError({ statusCode: route.query.error_code, message: route.query.error_description })
}

onMounted(() => {
  useModal().open(resolveComponent('ModalAuthPasswordNew'), null, null, {width: {min: 420}})
})
</script>

<template>
  <div>Change password</div>
</template>