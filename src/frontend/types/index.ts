// ~/types/index.ts

export { };

declare global {
  type Modal = "review" | "cart" | "menuMobile" | "signInSocial" | "signInEmail" | "resetPassword" | "changePassword" | "logInEmail" | "logInPassword" | "logOut"

  type ModalObject = {
    isShow: Boolean,
    data?: Object | Object[] | null
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
    composition?: string
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