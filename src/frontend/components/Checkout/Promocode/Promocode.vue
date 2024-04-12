<script setup>
import {useCartStore} from '~/store/cart'
import {usePromocodeStore} from '~/store/promocode'

const {t} = useI18n()
const props = defineProps({})

const emit = defineEmits(['update:promocode'])

const isPromocodeActive = ref(false)
// const promocode = ref(null)
// const code = ref(null)

// COMPUTEDS
const order = computed(() => {
  console.log('useCartStore().order.promocode', useCartStore().order)
  return useCartStore().order
})

const promocode = computed(() => {
  return useCartStore().promocode
})

// METHODS
const confirmRemovePromocodeCallback = () => {
  useCartStore().setPromocode(null)
  useCartStore().removeCode()
  isPromocodeActive.value = false
  // code.value = null
  
  emit('update:promocode', null)

  useNoty().setNoty({
    content: t('noty.promocode.removed', {code: order.value.promocode})
  }, 5000)
}

const disableRemovePromocodeCallback = () => {

}

// HANDLERS
const removePromocodeHandler = () => {
  useModal().open(resolveComponent('ModalConfirm'), {
    title: 'Отмена промокода',
    desc: 'Вы точно желаете отменить приминение промокод?',
    yes: {
      title: 'Удалить',
      callback: confirmRemovePromocodeCallback
    },
    no: {
      title: 'Отмена',
      callback: disableRemovePromocodeCallback
    }
  }, null, {
    width: {
      max: 420
    }
  })
}

const togglePromocodeHandler = () => {
  isPromocodeActive.value = !isPromocodeActive.value
}

const applyPromocodeHandler = async () => {
  console.log('code', order.value.promocode)
  await usePromocodeStore().show(order.value.promocode).then((res) => {
    if(res) {
      useCartStore().setPromocode(res)
      isPromocodeActive.value = false
      emit('update:promocode', order.value.promocode)

      useNoty().setNoty({
        content: t('noty.promocode.success', {code: order.value.promocode}),
        type: 'success'
      }, 5000)
    }
  }).catch((e) => {
    console.log('e', e)

    useNoty().setNoty({
      content: e.message,
      type: 'error'
    }, 7000)
  })
}
// WATCHERS
</script>

<style src='./promocode.scss' lang='scss' scoped></style>
<!-- <i18n src='' lang='yaml'></i18n> -->

<template>
  <div class="promocode-wrapper">
    <transition name="fade-in">
      <div v-if="promocode" class="promocode-card">
        <div class="promocode-header">
          <div class="promocode-name">
            {{ promocode.name }}
          </div>
          <button @click="removePromocodeHandler" class="buttom small promocode-remove-btn">
            <IconCSS name="iconoir:trash" size="14px"></IconCSS>
          </button>
        </div>
        <div class="promocode-desc">
          <simple-button-text
            :text="promocode.code"
            icon="fluent:tag-28-regular"
            class="promocode-action">
          </simple-button-text>
          <div class="promocode-sale">Скидка {{ promocode.value }}%</div>
        </div>
      </div>
      <simple-button-text
        v-else-if="!isPromocodeActive"
        @click="togglePromocodeHandler"
        :text="t('messages.use_promocode')"
        icon="fluent:tag-28-regular"
        class="promocode-action">
      </simple-button-text>
      <div v-else class="promocode-form">
        <form-text v-model="order.promocode" :placeholder="t('messages.enter_promocode')"></form-text>
        <div class="promocode-btns">
          <button @click="togglePromocodeHandler" class="button mini secondary full">{{ t('button.cancel') }}</button>
          <button @click="applyPromocodeHandler" class="button mini violet full">{{ t('button.apply') }}</button>
        </div>
      </div>
    </transition>
  </div>
</template>