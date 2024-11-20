<script lang="ts" setup>
import { useMailingStore } from '@/stores/MailingStore'
import { MailingClient } from "@/stores/types/mailing";
import type {AccountClient} from "@/stores/types/accounts";

const { mailing } = defineProps<{ mailing: MailingClient }>()

const mailingStore = useMailingStore()

const switchState = async (value: any) => {
  if (value) {
    mailingStore.setState(mailing, 100, 'Connecting...')
  } else {
    mailingStore.setState(mailing, 200, 'Disconnecting...')
  }

  if (mailing) {
    const newAccount: MailingClient = mailingStore.getMailing(mailing) ?? mailing
    await mailingStore.setMailingState(newAccount)
  }

  mailingStore.getMailings();
}
</script>

<template>
  <IconBtn :readonly="mailingStore.getState(mailing)?.disabled">
    <VSwitch
      :model-value="mailingStore.getState(mailing)?.currentState"
      class="state-switch-account mx-auto"
      :loading="mailingStore.getState(mailing)?.loading"
      hide-details
      :indeterminate="mailingStore.getState(mailing)?.indeterminate"
      @update:model-value="switchState"
    />
  </IconBtn>
</template>

<style lang="scss">
.state-switch-account .v-switch__track {
  background-color: rgba(var(--v-theme-on-surface), var(--v-focus-opacity)) !important;
}
</style>
