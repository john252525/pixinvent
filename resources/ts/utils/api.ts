import { FetchContext, ofetch } from 'ofetch'
import { types } from 'sass'
import Error = types.Error

export const $api = ofetch.create({
  baseURL: /* import.meta.env.VITE_API_BASE_URL || */'/api',
  onRequest({ options }) {
    const accessToken = useCookie('accessToken').value
    if (accessToken) {
      options.headers = {
        ...options.headers,
        'Accept-Language': localStorage.getItem('locale') || 'en',
        Authorization: `Bearer ${accessToken}`,
        Accept: 'application/json',
      }
    }
    else {
      options.headers = {
       ...options.headers,
        'Accept-Language': localStorage.getItem('locale') || 'en',
        // Accept: 'application/json',
      }
    }
  },
  onRequestError({ response}){
    console.error(response)
  }
})
