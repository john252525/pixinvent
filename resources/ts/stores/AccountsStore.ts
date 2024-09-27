import { onBeforeMount } from 'vue'
import type { AccountClient, Source, Step } from '@/stores/types/accounts'
import { getI18n } from '@/plugins/i18n'

const { t } = getI18n().global

const storedSource = localStorage.getItem('source')
export const useAccountsStore = defineStore('accounts-store', () => {
  const accountStates = [
    { state: 'offline', value: null, label: t('stateOffline'), color: 'error', qrColor: null, currentState: 'offline', nextState: 'checkState()', disabled: false, loading: false },
    { state: 'initializing', value: 0, label: t('Account just started to start'), color: 'info', qrColor: null, currentState: 'offline', nextState: 'checkState()', disabled: false, loading: true },
    { state: 'connected', value: 2.2, label: t('QR code received'), color: 'warning', qrColor: 'success', currentState: '(account: any) => showQrCode(account)', nextState: 'showQrCode()', disabled: false, loading: false },
    { state: 'connected', value: 2.22, label: t('Код авторизации получен'), color:'warning', qrColor: null, currentState: 'waiting', nextState: 'checkState', disabled: false, loading: false },
    { state: 'connecting', value: 3, label: t('Can not update QR'), color: 'warning', qrColor: 'error', currentState: 'offline', nextState: 'updateQrState', disabled: false, loading: false },
    { state: 'disconnecting', value: 4, label: t('Can not update QR'), color: 'warning', qrColor: 'error', currentState: 'offline', nextState: 'updateQrState', disabled: false, loading: false },
    { state: 'connected', value: 2.3, label: t('Can not update QR'), color: 'warning', qrColor: 'error', currentState: 'offline', nextState: 'updateQrState', disabled: false, loading: false },
    { state: 'online', value: 5, label: t('Account started successfully & realtime init done'), qrColor: null, color:'success', currentState: 'connected', nextState: null, disabled: false, loading: false },
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
    return (account: AccountClient): Step | null => {
      const foundClient = accounts.value[source.value].find((client: AccountClient): client is AccountClient => client.login === account.login)
      return foundClient ? foundClient.step : null;
    };
  });

  function getState(account: AccountClient) {
    return accountStates.find(state => state.value == (account?.step?.value ?? null))
  }

  function setState(account: AccountClient, step: number|null) {
    const accountIndex = accounts.value[source.value].findIndex(client => client.login === account.login)
    if (accountIndex) {
      accounts.value[source.value][accountIndex].step = { value: step, message: '' }
    }
  }

  function getAccount(client: AccountClient) {
    return accounts.value[source.value].find((c: AccountClient) => c.login === client.login)
  }

  function getAccountIndex(client: AccountClient) {
    console.log(client)
    return accounts.value[source.value].findIndex(account => account.login === client.login)
  }

  function setAccount(client: AccountClient) {
    const accountIndex = getAccountIndex(client)
    if (accountIndex !== -1) {
      accounts.value[source.value][accountIndex] = client;
    }
  }

  function updateAccount(client: AccountClient) {
    console.log(client)
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
      onResponseError({ error }) {
        console.log(error?.message)
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
    updateAccount,
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
  }
})
