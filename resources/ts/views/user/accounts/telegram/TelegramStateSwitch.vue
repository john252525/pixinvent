<script lang="ts" setup>
import type { AccountClient } from '@/stores/types/accounts'
import { useAccountsStore } from '@/stores/AccountsStore'

const { account } = defineProps<{ account: AccountClient }>()


const accountStore = useAccountsStore()

const switchState = async (value: any) => {

  const step = value ? 100 : 200
  const message = value ? 'Connecting...' : 'Disconnecting...'

  accountStore.setState(account, step, message)

  await accountStore.setAccountState(account, value)
}
</script>

<template>
  <IconBtn :readonly="accountStore.getState(account)?.disabled">
    <VSwitch
      :model-value="accountStore.getState(account)?.currentState"
      :color="accountStore.getState(account)?.color"
      :loading="accountStore.getState(account)?.loading"
      class="state-switch-account mx-auto"
      hide-details
      :indeterminate="accountStore.getState(account)?.indeterminate"
      @update:model-value="switchState"
    />
  </IconBtn>
</template>

<style lang="scss">
.state-switch-account .v-switch__track {
  background-color: rgba(var(--v-theme-on-surface), var(--v-focus-opacity)) !important;
}
</style>
