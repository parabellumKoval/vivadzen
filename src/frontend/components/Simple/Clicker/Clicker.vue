<script setup>
  const x = ref(0)
  const y = ref(0)
  const isActive = ref(false)
  const timer = ref(null)

  const stylePosition = computed(() => {
    return `top: ${y.value}px; left: ${x.value}px`
  })

  onMounted(() => {
    window.addEventListener('click', (event) => {
      if(!event.target.closest("[clickable]"))
        return

      x.value = event.x - 50
      y.value = event.y - 50

      isActive.value = true

      clearTimeout(timer.value)

      timer.value = setTimeout(() => {
        isActive.value = false
      }, 600)

      //console.log('click', event.target, event.target.closest("[clickable]"), event)
    })
  })
</script>

<style src="./clicker.scss" lang="scss" scoped />

<template>
  <div class="clicker" :class="{'clicker--effect': isActive}" :style="stylePosition"></div>
</template>