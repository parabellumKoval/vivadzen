<script setup>
const {t} = useI18n()

const { scrollToAnchor } = useAnchorScroll({
  toAnchor: {
    scrollOptions: {
      behavior: 'smooth',
      offsetTop: -90,
    }
  },
})

const props = defineProps({
  breadcrumbs: {
    type: Array,
    default: []
  },
  filters: {
    type: Array,
    default: []
  },
  filtersMeta: {
    type: Object,
    default: null
  },
  products: {
    type: Array,
    default: []
  },
  meta: {
    type: Object,
    default: {}
  }
})

const emit = defineEmits([
  'update:filters',
  'update:order',
  'update:page'
])

const selectedFilters = ref({})
const sortSelectedIndex = ref(1)
const sort = ref({order_by: 'created_at', order_dir: 'desc'})

const router = useRouter()

// COMPUTED
const sortingOptions = computed(() => {
  return [
    {
      by: 'created_at',
      dir: 'asc',
      caption: 'По-возрастанию по-новизне'
    },{
      by: 'created_at',
      dir: 'desc',
      caption: 'По-убыванию по-новизне'
    },{
      by: 'price',
      dir: 'asc',
      caption: 'По-возрастанию цены'
    }, {
      by: 'price',
      dir: 'desc',
      caption: 'По-убыванию цены'
    }
  ]
})

// WATCH
watch(sortSelectedIndex, (v) => {
  if(sortingOptions.value[v]) {
    sort.value = {
      order_by: sortingOptions.value[v].by,
      order_dir: sortingOptions.value[v].dir
    }

    scrollHandler('title')
    emit('update:order', sort.value)
  }
})

// HANDLERS
const scrollHandler = (item) => {
  nextTick(() => {
    scrollToAnchor(item)
  });
}

const updatePageHandler = (v) => {
  scrollHandler('title')
  emit('update:page', v)
  // console.log('updatePageHandler', v)
}

const updateSelectedHandler = (v) => {
  scrollHandler('title')
  selectedFilters.value = v
  emit('update:filters', v)
}

// METHODS

</script>

<style src="~/assets/scss/layout-catalog.scss" lang="scss" scoped></style>

<template>
  <div class="page-base">
    <div class="container">
      <the-breadcrumbs :crumbs="breadcrumbs"></the-breadcrumbs>

      <div id="title" class="title-common">
        <!-- SLOT TITLE HERE -->
        <slot name="title" />
      </div>
    </div>

    <transition name="fade-in">
      <div v-if="Object.keys(selectedFilters).length" class="selected">
        <div class="container">
          <filter-selected
            :modelValue="selectedFilters"
            @update:modelValue="updateSelectedHandler"
            :filters="filters"
          ></filter-selected>
        </div>
      </div>
    </transition>

    <!-- SLOT HEADER HERE -->
    <slot name="header"></slot>

    <div class="container">
      <div class="header">
        <div v-if="meta?.total" class="header-title">
          Фильтры
        </div>
        <div class="header-desc">
          Найдено {{ meta?.total || 0 }} товаров
        </div>
        <div v-if="meta?.total" class="header-actions">
          <div class="sorting-wrapper">
            <button class="button mini light sorting-btn">
              <IconCSS name="iconoir:sort-down" class="inline-icon"></IconCSS>
              <span>{{ sortingOptions[sortSelectedIndex].caption }}</span>
            </button>
            <select v-model="sortSelectedIndex" class="sorting-select">
              <option
                v-for="(srt, key) in sortingOptions" 
                :key="key"
                :value="key"
              >
                {{ srt.caption }}
              </option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="content">
      <!-- All filters -->
      <filter-list
        v-if="filters"
        :modelValue="selectedFilters"
        @update:modelValue="updateSelectedHandler"
        :filters="filters"
        :meta="filtersMeta"
        class="filters"
      ></filter-list> 

      <!-- Products list -->
      <div class="content-grid">
        <transition-group name="fade-in">
          <product-card
            v-for="product in products"
            :key="product.id"
            :item="product"
            class="content-grid-item"
          ></product-card>
        </transition-group>
      </div>
    </div>

    <div v-if="meta" class="container">
      <div class="content">
        <div></div>
        <div>
          <!-- Pagination -->
          <simple-pagination
            v-if="meta.total >= meta.per_page"
            :total="meta.last_page"
            :current="meta.current_page"
            @update:current="updatePageHandler"
            class="pagination"
          ></simple-pagination>

          <!-- SLOT FOOTER HERE -->
          <slot name="footer" />

        </div>
      </div>
    </div>

    <filter-mobile-buttons v-if="$device.isMobile"></filter-mobile-buttons>

  </div>
</template>