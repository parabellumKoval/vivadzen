// ~/types/index.ts

export { };

declare global {
  type Modal = "review" | "cart" | "menuMobile" | "signInSocial" | "signInEmail" | "resetPassword" | "changePassword" | "logInEmail" | "logInPassword" | "logOut"

  type ModalObject = {
    isShow: Boolean,
    data?: Object | Object[] | null
  }
  
  type Image = {
    src: string,
    alt: string,
    title: string
  }

  type Auth = {
    email: string | null,
    password: string | null,
    password_confirmation?: string | null,
    communication?: string | null,
    communication_number?: string | null,
    firstname?: string | null,
    lastname?: string | null,
  }

  type LoginForm = {
    email: string | null,
    password: string | null,
  }

  type Profile = {
    id: number | string | null,
    email: string,
    fullname: string,
    firstname: string | null,
    lastname: string | null,
    photo: string | null,
    phone: string | null,
    telegram: string | null
  };

  type Brand = {
    id: number,
    name: string,
    slug: string
  };

  type Feedback = {
    name: string,
    phone: string,
    email: string,
    text: string,
    type: string,
  };

  type Category = {
    id: number,
    name: string,
    slug: string
  }

  type Product = {
    id: number,
    name: string,
    slug: string,
    image: {
      src: string,
      title: string,
      alt: string,
      size: string
    },
    price: number,
    oldPrice: number,
    currency: string,
    rating: {
      rating: number,
      rating_count: number,
      reviews_count: number,
      rating_detailes: number[]
    },
    category: Category,
    amount?: number,
    inStock: number,
    code: string,
    store_only?: boolean,
    storeOnly?: boolean,
    composition?: string,
    basePrice?: number,
    campaignDiscount?: number,
    campaign?: {
      id: number,
      name: string,
      slug: string,
      short_description?: string | null,
      conditions_html?: string | null,
      discount_percent?: number,
      is_timed?: boolean,
      starts_at?: string | null,
      ends_at?: string | null,
      show_timer_card?: boolean,
      show_timer_product?: boolean,
      horizontal_banner?: string | null,
      vertical_banner?: string | null,
      add_to_main_banner?: boolean,
      add_banner_to_catalog?: boolean,
      catalog_banner_frequency?: number | null,
      catalog_banner_position?: number | null,
      catalog_url?: string | null
    } | null
  }

  type Article = {
    id: number,
    title: string,
    slug: string,
    image: {
      src: string
    },
    time: number
  }

  type Review = {
    id: number,
    reviewable: {
      id: number,
      name: string,
      slug: string,
      class: string
    },
    owner: {
      id?: number,
      photo?: string,
      name: string,
      email?: string
    },
    text: string,
    rating: number,
    created_at: Date
  }

  type Langs = "en" | "ru"
}
