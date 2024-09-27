<script setup lang="ts">
import { getI18n } from '@/plugins/i18n'
import WhatsappAccounts from '@/views/user/accounts/whatsapp/WhatsappAccounts.vue'
import AddSource from '@/views/user/accounts/AddSource.vue'
import type { Source } from '@/stores/types/accounts'
import { useAccountsStore } from '@/stores/AccountsStore'

const { t } = getI18n()

const whatsappRef = ref<typeof WhatsappAccounts | null>(null)
const addSourceRef = ref<typeof AddSource | null>(null)

const accountsStore = useAccountsStore()

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
</script>

<template>
  <VCard min-height="500">
    <VCardItem>
      <VCardTitle class="d-flex align-center flex-row gap-1">
        {{ $t('Accounts List') }}

        <VSelect
          v-model="accountsStore.source"
          :items="['telegram', 'whatsapp']"
          variant="solo"
          max-width="120"
          class="mt-0 selects-accounts"
          :disabled="accountsStore.loading.accounts"
          flat
          @update:model-value="accountsStore.setSource($event as Source)"
        />
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

    <WhatsappAccounts
      ref="whatsappRef"
      v-if="accountsStore.source === 'whatsapp'"
      @loading="checkIt"
    />

    <!-- 👉 Overlay -->
    <VOverlay
      v-model="state.refreshAccounts"
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
