<script setup lang="ts">
import { globalI18n } from '@/plugins/i18n'
import WhatsappAccounts from '@/views/user/accounts/whatsapp/WhatsappAccounts.vue'
import AddSource from '@/views/user/accounts/AddSource.vue'
import { accountSources, type Source } from '@/stores/types/accounts'
import { useAccountsStore } from '@/stores/AccountsStore'
import TelegramAccounts from '@/views/user/accounts/telegram/TelegramAccounts.vue'

definePage({
  meta: {
    action: 'read',
    subject: 'accounts',
  },
})
const { t } = globalI18n()

const accountsStore = useAccountsStore()

const whatsappRef = ref<typeof WhatsappAccounts | null>(null)
const addSourceRef = ref<typeof AddSource | null>(null)

const refreshAccounts = ref(false)

const state = ref({
  source: localStorage.getItem('source') || 'telegram',
  refreshAccounts: false,
  addAccount: false,
})

const checkIt = (val: boolean) => {
  state.value.refreshAccounts = val
}

const onAdded = (login: any) => {
  if (state.value.source === 'whatsapp') {
    if (whatsappRef.value)
      whatsappRef.value.refreshAccounts()
    if (addSourceRef.value)
      addSourceRef.value.isActive = false
  }
}

onMounted(() => {
  accountsStore.getAccounts()
})
</script>

<template>
  <VCard min-height="500">
    <VCardItem>
      <VCardTitle class="d-flex align-center flex-row gap-1">
        {{ $t('Accounts List') }}

      </VCardTitle>

      <template #append>
        <!-- 👉 Add source button -->
        <AddSource
          ref="addSourceRef"
          :source="accountsStore.source"
          @on-added="onAdded"
        />
        <!-- 👉 Refresh button -->
        <IconBtn
          :disabled="accountsStore.loading.accounts"
          @click="accountsStore.getAccounts()"
        >
          <VIcon
            size="20"
            :icon="accountsStore.loading.accounts ? 'svg-spinners:clock' : 'tabler-refresh'"
          />
        </IconBtn>
      </template>
    </VCardItem>
    <VCardItem>
      <VSelect
        v-model="accountsStore.source"
        :items="accountSources.map(source => ({ value: source, title: $t(source) }))"
        variant="outlined"
        max-width="120"
        clearable
        prepend-inner-icon="mdi-account-group-outline"
        min-width="200"
        :disabled="accountsStore.loading.accounts"
        @update:model-value="accountsStore.setSource($event as Source)"
      >
        <template #item="{ item, props }">
          <VListItem v-bind="props" :disabled="item.value === 'crm' || item.value === 'sms' || item.value === 'helpdesk'" />
        </template>
      </VSelect>
    </VCardItem>

    <WhatsappAccounts
      ref="whatsappRef"
      v-if="accountsStore.source === 'whatsapp'"
      @loading="checkIt"
    />

    <TelegramAccounts
      ref="telegramRef"
      v-if="accountsStore.source === 'telegram'"
      @loading="checkIt"
    />

    <!-- 👉 Overlay -->
    <VOverlay
      v-model="refreshAccounts"
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
