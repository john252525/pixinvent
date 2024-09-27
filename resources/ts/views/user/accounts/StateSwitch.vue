<script lang="ts" setup>
import type { AccountClient } from '@/stores/types/accounts'
import { useAccountsStore } from '@/stores/AccountsStore'

const props = defineProps(['source'])
const modelValue = defineModel<AccountClient>()

const emits = defineEmits(['checkState'])

const accountStore = useAccountsStore()

const states = {
  offline: {
    state: false,
    loading: false,
    color: 'secondary',
    disabled: false,
    readonly: false,
  },
  online: {
    state: true,
    loading: false,
    color: 'success',
    disabled: false,
    readonly: false,
  },
  connected: {
    state: true,
    loading: false,
    color: 'warning',
    disabled: false,
    readonly: false,
  },
  connecting: {
    state: true,
    loading: 'warning',
    color: 'secondary',
    disabled: true, // TODO: dev only
    readonly: false,
  },
  disconnecting: {
    state: true,
    loading: 'error',
    color: 'error',
    disabled: true, // TODO: dev only
    readonly: false,
  },
}

const stateStep = ref('offline')
const state = ref(false)

const getState = computed(() => accountStore.getState(modelValue.value))

watch(accountStore.getAccount(modelValue.value), (currentState: any) => {
  // console.log(currentState)
  if (currentState?.account?.step?.value === 5) {
    state.value = true
    stateStep.value = 'online'
  } else if (currentState?.account?.step?.value === 2.2) {
    stateStep.value = 'connected'
    state.value = true
  } else if (currentState?.account?.step?.value === 2.3) {
    stateStep.value = 'connected'
    state.value = true
  } else if (currentState?.account?.step?.value === 2.22) {
    stateStep.value = 'connected'
    state.value = true
  } else if (currentState?.account?.step?.value === 0) {
    stateStep.value = 'connected'
    state.value = true
  } else {
    stateStep.value = 'offline'
    state.value = false
  }
}, { deep: true })

const switchState = async (value: boolean) => {
  // accountStore.setState(modelValue, value ? 3 : 4)
  const account = accountStore.accounts[accountStore.source].find((client: AccountClient) => client.login === modelValue.value.login)
  if (account) {
    if (value) {
      account.step = {
        value: 3,
        message: 'Connecting...',
      }
    } else {
      account.step = {
        value: 4,
        message: 'Disconnecting...',
      }
    }
  }

  let switchStatus = await accountStore.switchState(modelValue.value as AccountClient)
  console.log(switchStatus)
}

onMounted(() => {
  const stateMapping = {
    5: { step: 'online', state: states.online.state },
    2.2: { step: 'connected', state: states.connected.state },
    2.3: { step: 'connected', state: states.connected.state },
  };

  const currentState = stateMapping[getState.value?.value] || { step: 'offline', state: states.offline.state };

  stateStep.value = currentState.step;
  state.value = currentState.state;
})
</script>

<template>
  <VSwitch
    :model-value="states[accountStore.getState(modelValue)?.state].state"
    :color="states[accountStore.getState(modelValue)?.state].color"
    :disabled="states[accountStore.getState(modelValue)?.state].disabled"
    :readonly="states[accountStore.getState(modelValue)?.state].readonly"
    :loading="states[accountStore.getState(modelValue)?.state].loading"
    hide-details
    @update:model-value="switchState"
  />
</template>
