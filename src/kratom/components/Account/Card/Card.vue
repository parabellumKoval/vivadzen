<script setup lang="ts">
const { user, avatar, displayName } = useAuth()

const fallback = '/images/avatars/no.png'
const imageSrc = ref(avatar.value || fallback)

watch(
  avatar,
  (value) => {
    imageSrc.value = value || fallback
  },
  { immediate: true },
)

const name = computed(() => {
  if (displayName.value) return displayName.value
  return [user.value?.first_name, user.value?.last_name].filter(Boolean).join(' ')
})

const handleImageError = () => {
  imageSrc.value = fallback
}
</script>

<template>
  <div v-if="user" class="account-card">
    <nuxt-img
      :src="imageSrc"
      width="72"
      height="72"
      format="webp"
      quality="70"
      fit="cover"
      class="account-card__avatar"
      @error="handleImageError"
    />

    <div class="account-card__content">
      <div class="account-card__title">{{ name || user.email }}</div>
      <div class="account-card__meta">{{ user.email }}</div>
      <div v-if="user.phone" class="account-card__meta">{{ user.phone }}</div>
    </div>

    <slot />
  </div>
</template>

<style scoped lang="scss">
.account-card {
  display: grid;
  grid-template-columns: 72px minmax(0, 1fr);
  gap: 16px;
  align-items: center;
}

.account-card__avatar {
  width: 72px;
  height: 72px;
  border-radius: 22px;
  object-fit: cover;
  background: rgba(53, 82, 74, 0.12);
}

.account-card__content {
  display: grid;
  gap: 4px;
}

.account-card__title {
  color: #1f2b1d;
  font-size: 20px;
  font-weight: 700;
  line-height: 1.1;
}

.account-card__meta {
  color: #667160;
  font-size: 14px;
  line-height: 1.5;
  word-break: break-word;
}
</style>
