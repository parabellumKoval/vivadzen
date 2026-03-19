export default defineNuxtRouteMiddleware(() => {
  if (process.server) {
    return;
  }

  const appVersion = useCookie(
    'appVersion'
  )

  if(appVersion.value != useRuntimeConfig().public.appVersion) {
    // console.log('clear', appVersion.value, useRuntimeConfig().public.appVersion)

    // clear localStorage
    localStorage.clear()

    // clear session
    sessionStorage.clear();

    // clear all cookies
    var cookies = document.cookie.split(";");

    for (var i = 0; i < cookies.length; ++i) {
        var myCookie = cookies[i];
        var pos = myCookie.indexOf("=");
        var name = pos > -1 ? myCookie.substr(0, pos) : myCookie;
        document.cookie = name + "=;expires=Thu, 01 Jan 1970 00:00:00 GMT";
    }

    // set new app version
    appVersion.value = useRuntimeConfig().public.appVersion

    // reload page
    location.reload(true);
  }
})
