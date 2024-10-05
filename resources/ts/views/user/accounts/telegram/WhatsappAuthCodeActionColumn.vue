<script setup lang="ts">
import { getI18n } from '@/plugins/i18n'
import type { AccountClient } from '@/stores/types/accounts'
import { useAccountsStore } from '@/stores/AccountsStore'
import { useUserStore } from '@/stores/UserStore'
import { useClipboard } from '@vueuse/core'

const props = defineProps<{ account: AccountClient }>()

const { t } = getI18n().global
const accountsStore = useAccountsStore()
const userStore = useUserStore()
const { copy } = useClipboard()

const copyText = (text: string) => {
  copy(text)
  userStore.toast.success(t('text.copied-to-clipboard'))
}

const loading = reactive({
  getCode: false,
  updateCode: false,
  checkCode: false,
})
type LoadingState = 'getCode' | 'updateCode' | 'checkCode';
const getAuthCode = async (account: AccountClient, loadingState: LoadingState) => {
  loading[loadingState] = true
  await accountsStore.getAuthCode(account)
  loading[loadingState] = false
}
const getInfo = async (account: AccountClient) => {
  loading.checkCode = true
  await accountsStore.getInfo(account)
  loading.checkCode = false
}
</script>

<template>
  <VListItem>
    <VListItemSubtitle v-if="props.account.phone_code">
      Код авторизации WhatsApp:
      <VBtn
        variant="text"
        color="info"
        size="small"
        @click="copyText(props.account.phone_code)"
      >
        {{ props.account.phone_code }}
      </VBtn>
    </VListItemSubtitle>
    <VListItemSubtitle v-else>
      {{ $t('accounts.whatsapp.auth-code-column-text') }}
    </VListItemSubtitle>
    <template #append>
      <IconBtn
        v-if="!props.account.phone_code" @click="getAuthCode(props.account, 'getCode')"
        color="default"
        variant="text"
        :readonly="loading.getCode"
      >
        <VTooltip :text="$t('account.whatsapp.get-auth-code')">
          <template #activator="{ props }">
            <VIcon
              v-bind="props"
              :icon="loading.getCode ? 'svg-spinners:clock' : 'tabler-http-get'"
            />
          </template>
        </VTooltip>
      </IconBtn>
      <div v-else>
        <IconBtn :readonly="loading.updateCode" @click="getAuthCode(props.account, 'updateCode')">
          <VTooltip :text="$t('account.whatsapp.update-auth-code')">
            <template v-slot:activator="{ props }">
              <VIcon v-bind="props" :icon="loading.updateCode ? 'svg-spinners:clock' : 'mdi-refresh'"/>
            </template>
          </VTooltip>
        </IconBtn>
        <IconBtn :readonly="loading.checkCode" @click.stop="getInfo(props.account)">
          <VTooltip
            :text="t('account.whatsapp.check-auth-code')">
            <template #activator="{ props }">
              <VIcon v-bind="props" :icon="loading.checkCode ? 'svg-spinners:clock' : 'mdi-check'" />
            </template>
          </VTooltip>
        </IconBtn>
      </div>
    </template>
  </VListItem>
</template>
