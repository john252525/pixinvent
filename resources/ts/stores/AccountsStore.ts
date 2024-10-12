import { onBeforeMount } from 'vue'
import type { AccountClient, TelegramClient, Source, Step } from '@/stores/types/accounts'
import { getI18n } from '@/plugins/i18n'

const { t } = getI18n().global

const storedSource = localStorage.getItem('source')
export const useAccountsStore = defineStore('accounts-store', () => {
  const accountStates = [
    { state: 'disconnected', value: null, label: t('stateOffline'), color: 'red', currentState: false, disabled: false, loading: false, indeterminate: false },
    { state: 'initializing', value: 0, label: t('Account just started to start'), color: 'info', currentState: true, disabled: false, loading: 'info', indeterminate: false },
    { state: 'force-stopping', value: 0.1, label: t('Account force stopping'), color: 'red', currentState: false, disabled: false, loading: false, indeterminate: false },
    { state: 'connected', value: 2.2, label: t('QR code received'), color: 'warning', currentState: true, disabled: false, loading: false, indeterminate: false },
    { state: 'connected', value: 2.22, label: t('Код авторизации получен'), color:'warning', currentState: true, disabled: false, loading: false, indeterminate: false },
    { state: 'connected', value: 2.25, label: t('Код авторизации получен'), color:'warning', currentState: true, disabled: false, loading: false, indeterminate: false },
    { state: 'connected', value: 2.3, label: t('Can not update QR'), color: 'warning', currentState: true, disabled: false, loading: false, indeterminate: false },
    { state: 'connecting', value: 100, label: t('Connecting...'), color: 'secondary', currentState: false, disabled: true, loading: 'warning', indeterminate: true },
    { state: 'disconnecting', value: 200, label: t('Disconnecting...'), color: 'secondary', currentState: false, disabled: true, loading: 'error', indeterminate: true },
    { state: 'online', value: 5, label: t('Account started successfully & realtime init done'), color: 'success', currentState: true, disabled: false, loading: false, indeterminate: false },
  ]

  const source = ref<Source>(storedSource === 'whatsapp' || storedSource === 'telegram' ? storedSource : 'telegram')
  const accounts = ref<Record<Source, AccountClient[]>>({
    whatsapp: [],
    telegram: [],
    crm: [],
    sms: [],
    helpdesk: [],
  })

  const qrCode = ref('')
  const loading = ref({
    account: false,
    accounts: false,
    qrCode: false,
  })

  const total = computed(() => accounts.value[source.value]?.length || 0)

  const clearData = () => {
    source.value = 'telegram'
    accounts.value['telegram'] = []
    accounts.value['whatsapp'] = []
    qrCode.value = ''
  }

  const getStep = computed(() => {
    return (account: AccountClient): Step | null => getAccount(account)?.step || { value: null, message: 'disconnected' }
  })

  function getState(account: AccountClient) {
    return accountStates.find(state => state.value === (account?.step?.value ?? null))
  }

  function setState(account: AccountClient, stepValue: number, stepMessage: string) {
    const currentAccount = getAccount(account)
    if(currentAccount)
      currentAccount.step = { value: stepValue, message: stepMessage }
  }

  function setAccountStateMessage(account: AccountClient, message: string): void {
    const currentAccount = getAccount(account)
    if (currentAccount && currentAccount.step) {
      currentAccount.step.message = message
    }
  }

  async function setAccountState(account: AccountClient, state = true, updateAccount: boolean = true) {
    const action = 'set-state'

    return await $api(`user/sources/${source.value}/set-state`, {
      method: 'POST',
      body: { account, state, action },
      onResponse({ response }) {
        if (updateAccount)
          setAccount(response._data)

        return response._data
      },
      onResponseError({ response }) {
        console.log(response)
        getAccounts()
      },
    })
  }

  function getAccount(account: AccountClient) {
    const accountIndex = getAccountIndex(account)
    return accountIndex !== -1 ? accounts.value[source.value][accountIndex] : accounts.value[source.value].find((client: AccountClient) => client.login === account.login)
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
      async onResponseError({ response}) {
        console.log(response)
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
      onResponseError({ response }) {
        console.log(response)
      },
    })
  }

  async function solveChallenge(account: TelegramClient, code: string) {
    const action = 'solve-challenge'

    return await $api(`/user/sources/${source.value}/solve-challenge`, {
      method: 'POST',
      body: { account, action, code },
      onResponse: ({ response }) => {
        setAccount(response._data)
      },
      onResponseError({ response }) {
        console.log(response)
      },
    })
  }

  async function sendTwoFactorAuth(account: TelegramClient, code: string) {
    const action = 'send-two-factor-auth'

    return await $api(`/user/sources/${source.value}/send-two-factor-auth`, {
      method: 'POST',
      body: { account, action, code },
      onResponse: ({ response }) => {
        setAccount(response._data)
      },
      onResponseError({ response }) {
        console.log(response)
      },
    })
  }

  async function sendTelegramCode(account: TelegramClient | undefined) {
    if(!account)
      return

    const action = 'send-telegram-code'

    return await $api(`/user/sources/${source.value}/send-telegram-code`, {
      method: 'POST',
      body: { account, action },
      onResponse: ({ response }) => {
        setAccount(response._data)
      },
      onResponseError({ response }) {
        console.log(response)
      },
    })
  }

  async function switchAuth(account: AccountClient, phone: any, type: string | null = null) {
    const action = 'switch-auth'

    return await $api(`/user/sources/${source.value}/switch-auth`, {
      method: 'POST',
      body: { account, phone, action, type },
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

  async function switchState(account: AccountClient, state = true, updateAccount: boolean) {
    const action = 'switch-state'

    return await $api(`user/sources/${source.value}/switch-state`, {
      method: 'POST',
      body: { account, action, state },
      onResponse({ response }) {
        if (updateAccount)
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
    const action = 'get-qr-code'

    return await $api(`user/sources/${source.value}/get-qr-code`, {
      method: 'POST',
      body: { account, action },
      onResponse({ response }) {
        return setAccount(response._data)
      },
      async onResponseError({ response}) {
        console.log(response)
        await getAccounts()
      }
    })
  }

  async function getQr(account: AccountClient) {
    const action = 'get-qr'

    return await $api(`user/sources/${source.value}/get-qr`, {
      method: 'POST',
      body: { account, action },
      onResponse({ response }) {
        return setAccount(response._data)
      },
      async onResponseError({ response }) {
        console.log(response)
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
      async onResponseError({ response }) {
        console.log(response)
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
    // await getAccounts()
  })

  onUnmounted(() => {
    console.log('onUnmounted')
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
    setAccountState,
    sendTelegramCode,
    solveChallenge,
    sendTwoFactorAuth,
    getQr,
    setAccountStateMessage,
    clearData,
  }
})
