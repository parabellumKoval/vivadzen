export default defineEventHandler(async (event) => {
  const body = await readBody(event)
  
  return await $fetch(`https://api.novaposhta.ua/v2.0/json/`, {
    method: 'POST',
    body: {
      "apiKey": useRuntimeConfig().public.novaposhtaKey,
      "modelName": "Address",
      "calledMethod": "searchSettlementStreets",
      "methodProperties": {
        "Page" : "1",
        "StreetName" : body?.find,
        "SettlementRef" : body?.city,
        "Limit" : "50"
      }
    }
  })
})