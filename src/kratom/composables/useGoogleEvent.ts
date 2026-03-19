type Product = {
  id: number,
  name: string,
  price: number,
  brand: Brand,
  categories: Category[],
  amount?: number,
  code: string,
  index?: number,
  item_list_name?: string,
  item_list_id?: string,
}

type ProductEcommerce = {
  item_id: number | string,
  item_name: string,
  price: number,
  item_brand: string,
  item_category: string,
  item_category2?: string,
  item_category3?: string,
  item_category4?: string,
  item_list_name?: string,
  item_list_id?: string,
  quantity?: number,
  item_variant?: string,
  index?: number,
}

// interface ItemData {
//   item_id: string,
//   item_name: string,
//   item_brand: string,
//   price: number,
//   quantity?: number,
//   [key: string]: string | number | undefined,
// }

// Общий интерфейс для всего объекта data
interface EcommerceData {
  currency: string;
  value: number;
  items: ProductEcommerce[];
}

// type EventName = 'AddToCart' | 'ViewItem' | 'RemoveFromCart' | 'BeginCheckout' | 'Purchase' | 'Refund'

type EventHandlers = {
  // [E in EventName as `get${E}Data`]: (product: EventPayloadMap[E]) => any;
  [E in EventName as `get${E}Data`]: (data: EventPayloadMap[E]) => {
    event: string;
    ecommerce: object;
  };
};

type EventName = keyof EventPayloadMap;

interface EventPayloadMap {
  АddShippingInfo: Checkout,
  АddPaymentInfo: Checkout,
  ViewItemList: ItemsList;
  SelectItem: ItemUnit;
  AddToCart: Product;
  RemoveFromCart: Product;
  ViewItem: Product;
  BeginCheckout: Checkout;
  Purchase: Checkout;
  Refund: Product;
}

type ItemUnit = {
  product: Product,
  id: string,
  name: string
}

type ItemsList = {
  products: Product[],
  id: string,
  name: string
}

type Checkout = {
  products: Product[],
  total: number,
  code?: string,
  shipping?: string,
  payment?: string,
}

export const useGoogleEvent = () => {

  const getProductUnitData = (product: Product, item_list_id: string | null = null, item_list_name: string | null = null) => {
    let data: ProductEcommerce = {
      item_id: product?.id,
      item_name: product?.name,
      price: formatPrice(product?.price)
    }


    if(product?.brand?.name) {
      data['item_brand'] = product?.brand?.name || ''
    }

    if(product?.amount) {
      data['quantity'] = product.amount
    }

    if(product?.categories) {
      product.categories.forEach((cat, index) => {
        data['item_category' + (index + 1)] = cat.name
      })
    }

    if(product?.index) {
      data['index'] = product.index
    }

    if(product?.item_list_name || item_list_name) {
      data['item_list_name'] = product.item_list_name || item_list_name
    }

    if(product?.item_list_id || item_list_id) {
      data['item_list_id'] = product.item_list_id || item_list_id
    }

    return data
  }


  const getItemData = (product: Product) => {
    let data: EcommerceData = {
        currency: "UAH",
        value: formatPrice(product?.price),
        items: [
          getProductUnitData(product)
        ]
    }

    return data
  }


  const formatPrice = (value: number | string): number => {
    const floatValue = parseFloat(String(value).replace(',', '.'));

    // Округляем до двух знаков после запятой
    return parseFloat(floatValue.toFixed(2));
  }

  const eventHandlers: EventHandlers = {
    getViewItemListData: (data) => {

      let items = data.products.map((item) => {
        return getProductUnitData(item, data.id, data.name)
      })

      return {
        event: "view_item_list",
        ecommerce: {
          item_list_name: data?.name,
          item_list_id: data?.id,
          items: items
        }
      }
    },
    getSelectItemData: (data) => {
      return {
        event: "select_item",
        ecommerce: {
          item_list_name: data?.name,
          item_list_id: data?.id,
          items: [getProductUnitData(data.product, data.id, data.name)]
        }
      }
    },
    getViewItemData: (product) => {
      return {
        event: "view_item",
        ecommerce: getItemData(product)
      }
    },
    getAddToCartData: (product) => {
      return {
        event: "add_to_cart",
        ecommerce: getItemData(product)
      }
    },
    getRemoveFromCartData: (product) => {
      return {
        event: "remove_from_cart",
        ecommerce: getItemData(product)
      }
    },
    getBeginCheckoutData: (data) => {
      let items = data.products.map((item) => {
        return getProductUnitData(item)
      })

      return {
        event: "begin_checkout",
        ecommerce: {
          currency: "UAH",
          value: formatPrice(data.total),
          items: items
        }
      }
    },
    getPurchaseData: (data) => {
      let items = data.products.map((item) => {
        return getProductUnitData(item)
      })

      return {
        event: "purchase",
        ecommerce: {
          transaction_id: data.code,
          value: formatPrice(data.total),
          currency: "UAH",
          items: items
        }
      }
    },
    getАddShippingInfoData: (data) => {
      let items = data.products.map((item) => {
        return getProductUnitData(item)
      })

      return {
        event: "add_shipping_info",
        ecommerce: {
          shipping_tier: data.shipping,
          currency: "UAH",
          value: formatPrice(data.total),
          items: items
        }
      }
    },
    getАddPaymentInfoData: (data) => {
      let items = data.products.map((item) => {
        return getProductUnitData(item)
      })

      return {
        event: "add_payment_info",
        ecommerce: {
          payment_type: data.payment,
          currency: "UAH",
          value: formatPrice(data.total),
          items: items
        }
      }
    },
  }

  const setEvent = <E extends EventName>(eventName: E, data: EventPayloadMap[E]) => {
    // const { gtag } = useGtag()

    // const functionName = `get${eventName}Data` as keyof EventHandlers
    // const handler = eventHandlers[functionName as keyof EventHandlers];

    // const gTagData = handler(data)
    // gtag('event', gTagData.event, gTagData.ecommerce)
  }

  return {
    setEvent
  }
}