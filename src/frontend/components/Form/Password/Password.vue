<script>
export default {
  data() {
    return {
      clearValue: '',
      isVisible: false
    }
  },

  props: {
    modelValue: {
      type: String
    },
    placeholder: {
      type: String
    },
    required: {
      type: Boolean,
      default: false
    },
    error: {
      type: [Object, Array, String, Boolean],
      default: false
    },
    isDisabled: {
      type: Boolean,
      default: false
    }
  },
  
  methods: {
    changeVisible(value) {
      this.clearValue = value
      this.$emit('update:modelValue', value)
    },

    changeInvisible(value) {
      const lastSymbol = value.slice(-1)

      if(lastSymbol === '*' || !lastSymbol){
        this.clearValue = this.clearValue.slice(0, -1)
      }else {
        this.clearValue += value.slice(-1)
      }

      this.$emit('update:modelValue', this.clearValue)
    },

    changeHandler(value) {
      if(this.isVisible){
        this.changeVisible(value)
        return
      }
      
      this.changeInvisible(value)
      
    },

    showHandler() {
      this.isVisible = !this.isVisible
    }
  },

  computed: {
    valueWithStars() {
      if(this.isVisible)
        return this.modelValue
      else
        return '*'.repeat(this.modelValue.length)
    }
  }
}
</script>

<style src="./password.scss" lang="sass" scoped />

<template>
  <form-text
    :model-value="valueWithStars"
    @update:modelValue="changeHandler"
    :placeholder="placeholder"
    :required="required"
    :error="error"
    :is-disabled="isDisabled"
  >
    <template v-slot:icon-right>
      <button @click="showHandler" :class="{active: isVisible}" clickable class="show-password">
        <IconCSS v-if="isVisible" name="iconoir:eye-alt" size="24px" class="icon"></IconCSS>
        <IconCSS v-else name="iconoir:eye-closed" size="24px" class="icon"></IconCSS>
      </button>
    </template>

  </form-text>
</template>