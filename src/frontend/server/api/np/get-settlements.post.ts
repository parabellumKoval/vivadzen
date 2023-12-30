export default defineEventHandler(async (event) => {
  const body = await readBody(event)
  
  return await $fetch(`https://api.novaposhta.ua/v2.0/json/`, {
    method: 'POST',
    body: {
      "apiKey": useRuntimeConfig().public.novaposhtaKey,
      "modelName": "Address",
      "calledMethod": "getSettlements",
      "methodProperties": {
        "Page" : "1",
        "Warehouse" : "1",
        "FindByString" : body?.find,
        "Limit" : "20"
      }
    }
  })
})