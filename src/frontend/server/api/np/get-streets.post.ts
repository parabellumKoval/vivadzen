export default defineEventHandler(async (event) => {
  const body = await readBody(event)
  
  let properties = {}

  // Find by string
  if(body.find){
    properties['StreetName'] = body.find
  }

  // Find by settlement ref
  if(body.settlementRef){
    properties['SettlementRef'] = body.settlementRef
  }

  return await $fetch(`https://api.novaposhta.ua/v2.0/json/`, {
    method: 'POST',
    body: {
      "apiKey": useRuntimeConfig().public.novaposhtaKey,
      "modelName": "Address",
      "calledMethod": "searchSettlementStreets",
      "methodProperties": {
        "Page" : "1",
        "Limit" : "50",
        ...properties
      }
    }
  })
})