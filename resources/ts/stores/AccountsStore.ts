import { onBeforeMount } from 'vue'
import type { AccountClient, Source, Step } from '@/stores/types/accounts'
import { getI18n } from '@/plugins/i18n'

const { t } = getI18n().global

const storedSource = localStorage.getItem('source')
export const useAccountsStore = defineStore('accounts-store', () => {
  const accountStates = [
    { state: 'disconnected', value: null, label: t('stateOffline'), color: 'error', currentState: false, disabled: false, loading: false },
    { state: 'initializing', value: 0, label: t('Account just started to start'), color: 'info', currentState: true, disabled: false, loading: 'info' },
    { state: 'connected', value: 2.2, label: t('QR code received'), color: 'warning', currentState: true, disabled: false, loading: false },
    { state: 'connected', value: 2.22, label: t('Код авторизации получен'), color:'warning', currentState: true, disabled: false, loading: false },
    { state: 'connected', value: 2.25, label: t('Код авторизации получен'), color:'warning', currentState: true, disabled: false, loading: false },
    { state: 'connected', value: 2.3, label: t('Can not update QR'), color: 'warning', currentState: true, disabled: false, loading: false },
    { state: 'connecting', value: 3, label: t('Can not update QR'), color: 'secondary', currentState: true, disabled: false, loading: 'warning' },
    { state: 'disconnecting', value: 4, label: t('Can not update QR'), color: 'secondary', currentState: false, disabled: false, loading: 'error' },
    { state: 'online', value: 5, label: t('Account started successfully & realtime init done'), color: 'success', currentState: true, disabled: false, loading: false },
  ]

  const source = ref<Source>(storedSource === 'whatsapp' || storedSource === 'telegram' ? storedSource : 'telegram')
  const accounts = ref<Record<Source, AccountClient[]>>({
    whatsapp: [],
    telegram: [],
  })

  const qrCode = ref('')
  const loading = ref({
    account: false,
    accounts: false,
    qrCode: false,
  })

  const total = computed(() => accounts.value[source.value].length ?? 0)

  const getStep = computed(() => {
    return (account: AccountClient): Step | null => getAccount(account)?.step || { value: null, message: 'disconnected' }
  })

  function getState(account: AccountClient) {
    return accountStates.find(state => state.value === (account?.step?.value ?? null))
  }

  function setState(account: AccountClient, stepValue: number, stepMessage: string) {
    const accountIndex = getAccountIndex(account)
    if (accountIndex !== -1)
      accounts.value[source.value][accountIndex].step = { value: stepValue, message: stepMessage }
  }

  function getAccount(account: AccountClient) {
    return accounts.value[source.value].find((client: AccountClient) => client.login === account.login)
  }

  function getAccountIndex(account: AccountClient) {
    return accounts.value[source.value].findIndex(client => account.login === client.login)
  }

  function setAccount(client: AccountClient) {
    const accountIndex = getAccountIndex(client)
    if (accountIndex !== -1) {
      accounts.value[source.value][accountIndex] = client;
    }
  }

  function addAccount(client: AccountClient) {
    accounts.value[source.value].push(client)
  }

  async function setSource(currentSource: Source) {
    source.value = currentSource
    localStorage.setItem('source', currentSource)
    await getAccounts()
  }

  async function getAuthCode(account: AccountClient) {
    const action = 'get-auth-code'

    return await $api(`/user/sources/${source.value}/get-auth-code`, {
      method: 'POST',
      body: { account, action },
      onResponse: ({ response }) => {
        setAccount(response._data)
      },
      async onResponseError() {
        await getAccounts()
      },
    })
  }

  async function forceStop(account: AccountClient) {
    const action = 'force-stop'

    return await $api(`/user/sources/${source.value}/force-stop`, {
      method: 'POST',
      body: { account, action },
      onResponse: ({ response }) => {
        setAccount(response._data)
      },
      onResponseError({ error }) {
        console.log(error?.message)
      },
    })
  }

  async function switchAuth(account: AccountClient, phone: number|string|undefined) {
    const action = 'switch-auth'

    return await $api(`/user/sources/${source.value}/switch-auth`, {
      method: 'POST',
      body: { account, phone, action },
      onResponse: ({ response }) => {
        setAccount(response._data)
      },
      async onResponseError({ error }) {
        await getAccounts()
      },
    })
  }
  async function getAccounts() {
    loading.value.accounts = true
    const action = 'get-accounts'

    await $api(`/user/sources/${source.value}`, {
      method: 'GET',
      query: { action },
      onResponse: ({ response }) => {
        accounts.value[source.value] = response._data.clients
      },
      onResponseError({ response }) {
        console.log(response)
        getAccounts()
      },
    })
    loading.value.accounts = false
  }

  async function switchState(account: AccountClient) {
    const action = 'switch-state'

    return await $api(`user/sources/${source.value}/switch-state`, {
      method: 'POST',
      body: { account, action },
      onResponse({ response }) {
        setAccount(response._data)
        return response._data
      },
      onResponseError({ response }) {
        getAccounts()
      },
    })
  }

  async function getInfo(account: AccountClient) {
    const action = 'get-info'

    return await $api(`user/sources/${source.value}/get-info`, {
      method: 'POST',
      body: { account, action },
      onResponse({ response }) {
        return setAccount(response._data)
      },
      onResponseError({ response }) {
        console.log(response)
      }
    })
  }

  async function getQrCode(account: AccountClient) {
    const action = 'get-info'

    return await $api(`user/sources/${source.value}/get-qr-code`, {
      method: 'POST',
      body: { account, action },
      onResponse({ response }) {
        return setAccount(response._data)
      },
      async onResponseError() {
        await getAccounts()
      }
    })
  }

  async function clearSession(account: AccountClient) {
    const action = 'clear-session'

    return await $api(`user/sources/${source.value}/clear-session`, {
      method: 'POST',
      body: { account, action },
      onResponse({ response }) {
        return setAccount(response._data)
      },
      async onResponseError() {
        await getAccounts()
      }
    })
  }

  async function updateAccount(account: AccountClient, type: string) {
    const action = 'update-account'

    return await $api(`user/sources/${source.value}`, {
      method: 'POST',
      body: { account, action, type },
      onResponse({ response }) {
        setAccount(response._data)
      },
      async onResponseError({ response }) {
        console.log(response)
        await getAccounts()
      }
    })
  }

  async function deleteAccount(account: AccountClient) {
    const action = 'delete-account'

    return await $api(`user/sources/${source.value}`, {
      method: 'DELETE',
      body: { account, action },
      onResponse({ response }) {
        accounts.value[source.value] = response._data.clients
      },
      async onResponseError({ response }) {
        console.log(response)
        await getAccounts()
      }
    })
  }

  onBeforeMount(async () => {
    await getAccounts()
  })

  return {
    // states
    accountStates,
    source,
    accounts,
    qrCode,
    loading,

    // getters
    total,
    getStep,

    // functions
    getAccounts,
    getAccount,
    setAccount,
    setSource,
    getState,
    forceStop,
    switchState,
    getInfo,
    setState,
    switchAuth,
    getAccountIndex,
    clearSession,
    getAuthCode,
    getQrCode,
    deleteAccount,
    addAccount,
    updateAccount,
  }
})
