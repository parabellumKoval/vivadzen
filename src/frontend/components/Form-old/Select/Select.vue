<script>
export default {
  setup() {
    const { t } = useI18n({useScope: 'local'})

    return {t}
  },

  data() {
    return {
      isActive: false
    }
  },

  props: {
    nullable: {
      type: Boolean,
      default: true
    },
    modelValue: {
      type: String
    },
    placeholder: {
      type: String,
    },
    required: {
      type: Boolean,
      default: false
    },
    values: {
      type: [Array, Object],
      required: true
    },
    error: {
      type: [Object, Array, String, Boolean],
      default: false
    },
  },

  computed: {
    placeholderIsActive() {
      return this.isActive || this.modelValue?.length
    },

    id() {
      return 'select-' + (Math.random() + 1).toString(36).substring(7);
    }
  },

  methods: {
    selectHandler(val) {
      this.$emit('update:modelValue', val)
    },
    focusHandler() {
      this.isActive = true
    },
    blurHandler() {
      this.isActive = false
    }
  }
}
</script>

<style src="./select.scss" lang="sass" scoped />

<template>
  <div :class="{active: isActive}" class="input__wrapper general-drop">
      <input
        :value="modelValue"
        @focus="focusHandler"
        @blur="blurHandler"
        :id="id"
        type="text"
        class="main-input"
        readonly 
        required
      > 
      
      <form-placeholder
        v-if="placeholder"
        :is-active="placeholderIsActive"
        :placeholder="placeholder"
        :is-required="required"
        :target-id="id"
      >
      </form-placeholder>

      <span class="icon-drop">
        <img src="~assets/svg-icons/arrow-simple.svg" class="icon" />
      </span>

      <form-error :error="error"></form-error>

      <div class="general-drop__list" scrollable>
        
        <div
          v-if="nullable"
          @click="selectHandler(null)"
          clickable
          class="general-drop__item"
        >
          <div class="text">{{ t('Please_select') }}</div>
        </div>

        <div
          v-for="(value, index) in values"
          :key="index"
          @click="selectHandler(value)"
          :class="{active: modelValue === value}"
          clickable
          class="general-drop__item"
        >
          <span class="icon-active"></span>
          <div class="text">{{ value }}</div>
        </div>

      </div>
  </div>
</template>

<i18n>
  {
    "en": {
      "Please_select" : "Please select",
    },
    "ru": {
      "Please_select" : "Выберите вариант",
    }
  }
</i18n>