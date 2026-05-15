<script setup lang="ts">
import AdultoGuideContent from '~/components/Modal/AdultoGuide/AdultoGuideContent.vue'

const { t } = useI18n()
</script>

<style scoped lang="scss">
.adulto-guide-modal {
  :deep(.modal-inner) {
    padding: 24px;

    @include desktop {
      padding: 32px;
    }
  }
}

@include mobile {
  .adulto-guide-modal {
    :deep(.modal-inner) {
      padding: 20px 16px;
    }
  }
}
</style>

<template>
  <modal-wrapper :title="t('kratom.product.adulto_modal_title')" class="adulto-guide-modal">
    <AdultoGuideContent placement="modal" default-version="short" />
  </modal-wrapper>
</template>
