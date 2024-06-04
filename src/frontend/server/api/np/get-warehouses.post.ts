export default defineEventHandler(async (event) => {
  const body = await readBody(event)
  
  let properties = {}

  // Find by string
  if(body.find){
    properties['FindByString'] = body.find
  }

  // Find by settlement ref
  if(body.settlementRef){
    properties['SettlementRef'] = body.settlementRef
  }

  return $fetch(`https://api.novaposhta.ua/v2.0/json/`, {
    method: 'POST',
    body: {
      "apiKey": useRuntimeConfig().public.novaposhtaKey,
      "modelName": "AddressGeneral",
      "calledMethod": "getWarehouses",
      "methodProperties": {
        "Page" : "1",
        "Limit" : "100",
        ...properties
      }
    }
  })
})