<script lang="ts" setup>
import type { AccountClient } from '@/stores/types/accounts'

const props = defineProps<{ account: AccountClient }>()

const accountsStore = useAccountsStore()

const showClearSessionDialog = ref(false)
const showClearSessionDialog2 = ref(false)
const loading = ref(false)

const doClearSession = async () => {
  loading.value = true
  const isReady = await accountsStore.clearSession(props.account)
  if (isReady) {
    loading.value = false
    showClearSessionDialog.value = false
  }
}
</script>

<template>
  <VDialog
    v-model="showClearSessionDialog"
    persistent
    max-width="500"
    >
    <template #activator>
      <div>
        <IconBtn
          :disabled="props.account.state"
          @click="showClearSessionDialog = true">
          <VIcon :icon="loading ? 'svg-spinners:clock' : props.account.state ? 'emojione-monotone:vertical-traffic-light' : 'emojione:vertical-traffic-light'"/>
        </IconBtn>
        <VTooltip
          :disabled="!props.account.state"
          activator="parent"
          text="Очистить сессию допускается только при выключенном состоянии"
        />
      </div>
    </template>
    <template #default="{ isActive }">
      <VCard>
        <VCardTitle>
          <VIcon size="24" icon="openmoji:stop-sign"/>
          <span class="title">Attention!!! CLEAR SESSION ALERT</span>
        </VCardTitle>
        <VCardText>
          <p>
            {{ $t('Are you sure you want to clear session and auth data of account?') }}
          </p>
        </VCardText>
        <VCardActions>
          <VBtn color="primary" variant="outlined" @click="isActive.value = false">
            Отмена
          </VBtn>
          <VDialog
            v-model="showClearSessionDialog2"
            persistent
            max-width="340"
          >
            <template #activator="{ props: activator2Props }">
              <VBtn color="error" variant="flat" v-bind="activator2Props">
                CLEAR SESSION
              </VBtn>
            </template>
            <template #default="{ isActive: isActive2 }">
              <VCard :loading title="ATTENTION!!! CLEAR SESSION">
                <VCardText>
                  {{ $t('Are you sure you want to clear session and auth data of account?') }}
                </VCardText>
                <VCardText>
                  {{ $t('This action is irreversible') }}
                </VCardText>
                <VCardActions>
                  <VSpacer></VSpacer>
                  <VBtn
                    text="Close"
                    variant="text"
                    @click="isActive2.value = false"
                  ></VBtn>
                  <VBtn
                    text="Clear Session"
                    color="error"
                    variant="flat"
                    @click="doClearSession"
                  ></VBtn>
                </VCardActions>
              </VCard>
            </template>
          </VDialog>
        </VCardActions>
      </VCard>
    </template>
  </VDialog>
</template>
