import {useNovaposhtaStore} from '~/store/novaposhta'
import type {Delivery} from '~/types/order';

export const useNp = (query: Object) => {

  const settlement = useState('settlementState', () => {return {}})
  const warehouse = useState('warehouseState', () => {return {}})
  const street = useState('streetState', () => {return {}})

  const settlementsData = ref([])
  const warehousesData = ref([])
  const streetsData = ref([])

  const isLoadingSettlements = ref(false)
  const isLoadingWarehouses = ref(false)
  const isLoadingStreets = ref(false)



  // COMPUTED
  const settlements = computed(() => {
    return settlementsData.value && settlementsData.value.map((item) => {
      return {
        value: `${item.Description} (${item.AreaDescription})`,
        settlement: item.Description,
        settlementRef: item.Ref,
        area: item.AreaDescription,
        region: item.RegionsDescription,
        type: item.SettlementTypeDescription
      }
    })
  })

  const warehouses = computed(() => {
    return warehousesData.value && warehousesData.value.map((item) => {
      return {
        warehouse: item.Description,
        warehouseRef: item.Ref
      }
    })
  })

  const streets = computed(() => {
    return streetsData.value && streetsData.value.map((item) => {
      return {
        street: item.Present,
        streetRef: item.SettlementStreetRef
      }
    })
  })

  // METHODS
  const setSettlement = (value: Delivery) => {
    settlement.value['settlement'] = value.settlement
    settlement.value['settlementRef'] = value.settlementRef
    settlement.value['area'] = value.area
    settlement.value['region'] = value.region
    settlement.value['type'] = value.type
  }

  const setWarehouse = (value: Delivery) => {
    warehouse.value['warehouse'] = value.warehouse
    warehouse.value['warehouseRef'] = value.warehouseRef
  }

  const setStreet = (value: Delivery) => {
    street.value['street'] = value.street
    street.value['streetRef'] = value.streetRef
  }

  const getWarehouses = async (search: String = null, settlementRef: String = null) => {
    isLoadingWarehouses.value = true

    return await useNovaposhtaStore().getWarehouses(search, settlementRef).then((res) => {
      if(res.data && res.data.length){
        warehousesData.value = res.data
      }else {
        warehousesData.value = []
      }

      return res.data
    }).finally(() => {
      isLoadingWarehouses.value = false
    })
  }

  const getSettlements = async (search: string = null, ref: string = null) => {
    isLoadingSettlements.value = true
    
    await useNovaposhtaStore().getSettlements(search, ref).then((res) => {
      if(res.data && res.data.length){
        settlementsData.value = res.data
      }else {
        settlementsData.value = []
      }
    }).finally(() => {
      isLoadingSettlements.value = false
    })
  }


  const getStreets = async (search: String = null, settlementRef: String = null) => {
    isLoadingStreets.value = true

    await useNovaposhtaStore().getStreets(search, settlementRef).then((res) => {
      if(res.data && res.data[0] && res.data[0]['Addresses'] && res.data[0]['Addresses'].length){
        streetsData.value = res.data[0]['Addresses']
      }else {
        streetsData.value = []
      }
    }).finally(() => {
      isLoadingStreets.value = false
    })
  }

  // WATCH
  // watch(() => settlement.value, (val) => {
  //   if(!val?.settlementRef)
  //     return

  //   setTimeout(() => {
  //     getWarehouses(null, val.settlementRef)
  //   }, 1000)
  // }, {
  //   deep: true,
  //   immediate: true
  // })

  return {
    getWarehouses,
    getSettlements,
    settlements,
    isLoadingSettlements,
    warehouses,
    isLoadingWarehouses,
    setSettlement,
    settlement,
    setWarehouse,
    warehouse,
    getStreets,
    isLoadingStreets,
    setStreet,
    street,
    streets
  }
}