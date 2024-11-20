<script setup lang="ts">
import { globalI18n } from '@/plugins/i18n'
import WhatsappAccounts from '@/views/user/accounts/whatsapp/WhatsappAccounts.vue'
import AddSource from '@/views/user/accounts/AddSource.vue'
import { accountSources, type Source } from '@/stores/types/accounts'
import { useAccountsStore } from '@/stores/AccountsStore'
import TelegramAccounts from '@/views/user/accounts/telegram/TelegramAccounts.vue'
import AddAutoSend from "@/views/user/mailing/AddAutoSend.vue";
import MailingItems from "@/views/user/mailing/MailingItems.vue";
import {useMailingStore} from "@/stores/MailingStore";

definePage({
  meta: {
    action: 'read',
    subject: 'mailing',
  },
})

const { t } = globalI18n()

const mailingStore = useMailingStore();

const addAutoSendRef = ref<typeof AddAutoSend | null>(null)
const mailingRef = ref<typeof MailingItems | null>(null)

const refreshMailing = ref(false)

const state = ref({
  source: mailingStore.getMailings(),
  refreshMailing: false,
})

const checkIt = (val: boolean) => {
  state.value.refreshMailing = val
}

const onAdded = () => {
  if (mailingRef.value)
    mailingRef.value.refreshMailing()
  if (addAutoSendRef.value)
    addAutoSendRef.value.isActive = false
}

onMounted(() => {
  mailingStore.getMailings()
})

</script>

<template>
  <VCard min-height="500">
    <VCardItem>
      <VCardTitle class="d-flex align-center flex-row gap-1">
        {{ $t('Mailing List') }}
      </VCardTitle>

      <template #append>
        <!-- 👉 Add source button -->

        <AddAutoSend
          ref="addAutoSendRef"
          @on-added="onAdded"
        />

        <!-- 👉 Refresh button -->
        <IconBtn
          :disabled="mailingStore.loading.mailings"
          @click="mailingStore.getMailings()"
        >
          <VIcon
            size="20"
            :icon="mailingStore.loading.mailings ? 'svg-spinners:clock' : 'tabler-refresh'"
          />
        </IconBtn>
      </template>
    </VCardItem>

    <MailingItems
      ref="mailingRef"
      @loading="checkIt"
    />

    <!-- 👉 Overlay -->
    <VOverlay
      v-model="refreshMailing"
      contained
      persistent
      scroll-strategy="none"
      class="align-center justify-center"
    >
      <VProgressCircular indeterminate />
    </VOverlay>
  </VCard>
</template>

<style lang="scss">
.selects-accounts {
  .v-field__input {
    padding: 0 0 1px 5px;
    font-weight: bold;
  }
}
</style>
