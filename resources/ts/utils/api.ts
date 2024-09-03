import { ofetch } from 'ofetch'

export const $api = ofetch.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || '/api',
  async onRequest({ options }) {
    const accessToken = useCookie('accessToken').value
    if (accessToken) {
      options.headers = {
        ...options.headers,
        'Accept-Language': localStorage.getItem('locale') || 'en',
        Authorization: `Bearer ${accessToken}`,
        // Accept: 'application/json',
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
})
