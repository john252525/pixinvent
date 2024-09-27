<script lang="ts" setup>
import type { AccountClient } from '@/stores/types/accounts'

const props = defineProps<{ account: AccountClient }>()

const accountsStore = useAccountsStore()

const showForceStopDialog = ref(false)
const loading = ref(false)

const doForceStop = async () => {
  showForceStopDialog.value = false
  loading.value = true
  const isReady = await accountsStore.forceStop(props.account)
  if (isReady) {
    loading.value = false
  }
}
</script>

<template>
  <VDialog
    v-model="showForceStopDialog"
    persistent
    max-width="500"
    >
    <template #activator>
      <IconBtn @click="showForceStopDialog = true">
        <VIcon :size="loading?20:28" :icon="loading ? 'svg-spinners:clock' : 'openmoji:stop-sign'"/>
      </IconBtn>
    </template>
    <template #default="{ isActive }">
      <VCard :loading>
        <VCardTitle>
          <VIcon icon="openmoji:stop-sign"/>
          <span class="title">Attention!!! Force stop</span>
        </VCardTitle>
        <VCardText>
          <p>
            {{ $t('Are you sure you want to force stop the account?') }}
          </p>
        </VCardText>
        <VCardActions>
          <VBtn color="primary" variant="outlined" @click="isActive.value = false">
            Отмена
          </VBtn>
          <VBtn
            color="error"
            variant="flat"
            :loading
            @click="doForceStop"
          >
            Принудительная остановка
          </VBtn>
        </VCardActions>
      </VCard>
    </template>

  </VDialog>
</template>
