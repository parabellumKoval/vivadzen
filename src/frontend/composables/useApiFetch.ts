export const useApiFetch = async (url: string, body: Object = null, method: string = 'GET') => {
  
  const locale = useNuxtApp().$i18n.locale

  const headers = {
    'Accept': 'application/json',
    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value,
    'X-Requested-With': 'XMLHttpRequest',
    'Accept-Language': locale.value
  };
  

  let data = null;
  let error = null;
  let exception = null;

  const options = {
    method: method,
    headers: headers,
    credentials: 'include',
    onResponse({response}) {
      //console.log('ON RESP', response, response._data, response.data )
      if(response.ok)
        data = response._data
      else
        error = response._data
    },
    async onResponseError({response}) {
      exception = response._data
    }
  }

  if(method === 'GET' && body) {
    const params = body? '?' + new URLSearchParams(body).toString(): '';
    url = url + params
  }else if(method === 'POST' && body) {
    options.body = JSON.parse(JSON.stringify(body))
  }

  await useFetch(url, options);

  return {
    data,
    error,
    exception
  }
}