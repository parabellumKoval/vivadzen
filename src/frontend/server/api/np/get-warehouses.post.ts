export default defineEventHandler(async (event) => {
  const body = await readBody(event)
  
  return $fetch(`https://api.novaposhta.ua/v2.0/json/`, {
    method: 'POST',
    body: {
      "apiKey": useRuntimeConfig().public.novaposhtaKey,
      "modelName": "AddressGeneral",
      "calledMethod": "getWarehouses",
      "methodProperties": {
        "Page" : "1",
        "Limit" : "100",
        "SettlementRef" : body?.city,
        "FindByString": body?.find
      }
    }
  })
})