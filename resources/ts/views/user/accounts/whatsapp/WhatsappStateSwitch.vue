<script lang="ts" setup>
import type { AccountClient } from '@/stores/types/accounts'
import { useAccountsStore } from '@/stores/AccountsStore'

const { account } = defineProps<{ account: AccountClient }>()


const accountStore = useAccountsStore()

const switchState = async (value: any) => {
  if (value) {
    accountStore.setState(account, 100, 'Connecting...')
  } else {
    accountStore.setState(account, 200, 'Disconnecting...')
  }

  if (account) {
    const newAccount: AccountClient = accountStore.getAccount(account) ?? account
    await accountStore.switchState(newAccount, value, true)
  }
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
