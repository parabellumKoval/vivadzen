<script setup>
  import { useAuthStore } from '~/store/auth';

  const { t } = useI18n() 

  const isLoading = ref(false)
  const email = ref('')
  const throttleTimer = ref(0)
  const throttleTimerId = ref(null)

  const errors = ref({})

  // COMPUTED

  const isDisabled = computed(() => {
    return throttleTimer.value > 0 || !email.value || email.value.length < 5
  })

  // METHODS
  const setThrottleTimeout = () => {
    throttleTimer.value = 30

    throttleTimerId.value = setInterval(function() {
      throttleTimer.value--

      if(throttleTimer.value <= 0)
        clearInterval(throttleTimerId.value)
    }, 1000)
  }

  // HANDLERS
  const backHandler = () => {
    useModal().open(resolveComponent('ModalAuthLogin'), null, null, {width: {min: 420, max: 420}})
  }

  const sendHandler = async () => {
  
    isLoading.value = true
    
    await useAuthStore().sendPasswordResetLink(email.value)
      .then(({data, error}) => {
        if(data !== null) {
          setThrottleTimeout()
          useNoty().setNoty({
            content: t('noty.auth.password.recovery.sent', {email: email.value})
          })
        }
        
        if(error) {
          useNoty().setNoty({
            content: t(`errors.${error.message}`),
            type: 'error'
          }, 7000)
        }
      })
      .finally(() => {
        isLoading.value = false
      })
  }

  // HOOKS
  onMounted(() => {
    email.value = useModal().active.data
  })
</script>

<style src="./../../auth.scss" lang="scss" scoped></style>
<style src="./reset.scss" lang="scss" scoped></style>

<i18n src="./lang.yaml" lang="yaml"></i18n>

<template>
  <modal-wrapper :title="t('title')" :description="t('reset_password')">
    <div class="modal-content-wrapper">
      <div class="popup__body">

        <form-text
          v-model="email"
          :placeholder="$t('form.email')"
          :errors="errors.email"
          class="form-component"
        >
        </form-text>

        <transition name="fade-in">
          <p
            v-if="throttleTimer"
            class="form-component"
            v-html="t('check_email', {email: email, sec: throttleTimer})"
          >
          </p>
        </transition>

        <button
          @click="sendHandler"
          :class="{loading: isLoading, disabled: isDisabled}"
          clickable
          class="button primary full"
        >
          <span class="text">{{ t('get_reset_link') }}</span>
        </button>
      </div>

      <div class="footer">
        <simple-button-text
          :text="t('button.back')"
          :callback="backHandler"
          icon="iconoir:arrow-left"
        ></simple-button-text>
      </div>
    </div>
  </modal-wrapper>
</template>