<template>
  <div class="product-card">
    <div class="skeleton product-card__image" />
    <div class="product-card__body">
      <div class="skeleton product-card__line" />
      <div class="skeleton product-card__line product-card__line--short" />
      <div class="skeleton product-card__price" />
      <div class="skeleton product-card__button" />
    </div>
  </div>
</template>

<style scoped>
.product-card {
  overflow: hidden;
  border-radius: var(--radius-lg);
  background: var(--color-white);
  box-shadow: var(--shadow-card);
}

.product-card__image {
  aspect-ratio: 1 / 1;
  border-radius: 0;
}

.product-card__body {
  padding: 10px;
}

.product-card__line {
  height: 14px;
  margin-bottom: 6px;
  border-radius: 4px;
}

.product-card__line--short {
  width: 60%;
  margin-bottom: 10px;
}

.product-card__price {
  width: 45%;
  height: 18px;
  margin-bottom: 10px;
  border-radius: 4px;
}

.product-card__button {
  height: 34px;
  border-radius: var(--radius-sm);
}
</style>
