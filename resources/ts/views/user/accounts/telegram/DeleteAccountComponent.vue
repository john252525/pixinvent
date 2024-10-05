<script lang="ts" setup>
import type { AccountClient } from '@/stores/types/accounts'
import { useAccountsStore } from '@/stores/AccountsStore'

const props = defineProps<{ account: AccountClient }>()

const accountsStore = useAccountsStore()

const loading = ref(false)
const showDeleteDialog = ref(false)

const showDialog = () => {
  nextTick(() => {
    showDeleteDialog.value = true
  })
}

const deleteAccount = async () => {
  loading.value = true
  await accountsStore.deleteAccount(props.account)
  loading.value = false
  showDeleteDialog.value = false
}
</script>

<template>
  <VBtn
    variant="text"
    :loading="loading"
    :prepend-icon="loading ? 'svg-spinners:clock' : 'mdi-delete'"
    @click="deleteAccount"
  >
    {{ $t('account.telegram.delete.button') }}
  </VBtn>
  <VDialog
    :ref="'refDeleteAccountDialog'"
    v-model="showDeleteDialog"
    persistent
    max-width="500"
    :key="props.account.login"
  >
    <template #activator>
      <VListItem
        v-if="0"
        :prepend-icon="loading ? 'svg-spinners:clock' : 'mdi-delete'"
        @click="showDialog"
      >
        удалить аккаунт
      </VListItem>
    </template>
    <template #default="{ isActive }">
      <VCard :loading>
        <VCardTitle>
          <VIcon icon="openmoji:stop-sign"/>
          <span class="title">{{ $t(`account.${accountsStore.source}.delete.title`) }}</span>
        </VCardTitle>
        <VCardText>
          <p>
            {{ $t(`account.${accountsStore.source}.delete.text`) }}
          </p>
        </VCardText>
        <VCardActions>
          <VBtn color="primary" variant="outlined" @click="isActive.value = false">
            {{ $t('Cancel') }}
          </VBtn>
          <VBtn
            color="error"
            variant="flat"
            :loading
            @click="deleteAccount"
          >
            {{ $t('account.delete.button') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </template>
  </VDialog>
</template>
