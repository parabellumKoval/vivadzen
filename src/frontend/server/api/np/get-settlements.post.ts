export default defineEventHandler(async (event) => {
  const body = await readBody(event)

  let properties = {}

  // Find by string
  if(body.find){
    properties['FindByString'] = body.find
  }

  // Find by settlement ref
  if(body.ref){
    properties['Ref'] = body.ref
  }
  
  return await $fetch(`https://api.novaposhta.ua/v2.0/json/`, {
    method: 'POST',
    body: {
      "apiKey": useRuntimeConfig().public.novaposhtaKey,
      "modelName": "Address",
      "calledMethod": "getSettlements",
      "methodProperties": {
        "Page" : "1",
        "Warehouse" : "1",
        "Limit" : "20",
        ...properties
      }
    }
  })
})