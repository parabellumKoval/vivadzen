export const useServerApiFetch = async (url: string, body: Object = null, method: string = 'GET') => {
  

  const getPairs = (obj, keys = []) =>
    Object.entries(obj).reduce((pairs, [key, value]) => {
      if (typeof value === 'object')
        pairs.push(...getPairs(value, [...keys, key]));
      else
        pairs.push([[...keys, key], value]);
      return pairs;
    }, []);


  const locale = useNuxtApp().$i18n.locale

  const headers = {
    'Accept': 'application/json',
    'X-XSRF-TOKEN': useCookie('XSRF-TOKEN').value,
    'X-Requested-With': 'XMLHttpRequest',
    'Accept-Language': locale.value
  };
  

  let data = null;
  let error = null;

  const options = {
    method: method,
    headers: headers,
    credentials: 'include'
  }

  if(method === 'GET' && body) {

    let x = getPairs(body)
      .map(([[key0, ...keysRest], value]) =>
        `${key0}${keysRest.map(a => `[${a}]`).join('')}=${value}`)
      .join('&');

    // const params = body? '?' + new URLSearchParams(body).toString(): '';
    const params = body? '?' + x: '';
    url = url + params
  }else if(method === 'POST' && body) {
    options.body = JSON.parse(JSON.stringify(body))
  }

  const response = await fetch(url, options)

  if(response.ok) {
    data = await response.json()
  }else {
    error = { 
      status: response.status,
      statusText: response.statusText,
      ...await response.json()
    }
  }

  return {
    data,
    error
  }
}