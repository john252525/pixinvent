<script lang="ts" setup>
import QRCodeVue3 from "qrcode-vue3"
import TelegramLazy from '@images/misc/telegram-lazy.png'
import { getI18n } from '@/plugins/i18n'
import { useAccountsStore } from '@/stores/AccountsStore'
import { useUserStore } from '@/stores/UserStore'
import { useClipboard } from '@vueuse/core'
import type { MaskaDetail, MaskInputOptions } from 'maska'
import type { TelegramClient } from '@/stores/types/accounts'

const account = defineModel<TelegramClient>('account')

const accountsStore = useAccountsStore()
const { toast } = useUserStore()

const { t } = getI18n().global
const { copy } = useClipboard()

const copyText = (text: string) => {
  copy(text)
  toast.success(t('text.copied-to-clipboard'))
}

const { pause, resume, isActive: isTimerActive } = useIntervalFn(async () => {

}, import.meta.env.VITE_QR_TIMEOUT * 1000)

const { ready, start, stop } = useTimeout(2000, { controls: true })

const loading = reactive({
  refresh: false,
  confirm: false,
})

const updateAccountLoader = ref(false)
const showPhoneDialog = ref(false)
const showQrDialog = ref(false)
const code = ref<string|undefined>(account.value?.additional.config.authPhone)
const phonePlaceholder = ref<number|string|undefined>(account.value?.additional.config.authPhone)
const stateText = ref()
const authMethod = ref<null|string>(null)

const refreshQr = async () => {
  if (!account.value)
    return

  loading.refresh = true
  accountsStore.accounts[accountsStore.source][accountsStore.getAccountIndex(account.value)].qr_code = undefined
  const result = await accountsStore.getQrCode(account.value)
  console.log(result)
  loading.refresh = false
}
const confirmQr = async () => {
  if (!account.value)
    return

  loading.confirm = true
  await accountsStore.getQrCode(account.value)
  loading.confirm = false
}

const switchAccountState = async (accountValue: any, state: boolean, updateAccount = true) => {
  stateText.value = t(state ? 'accounts.states.messages.switching.on' : 'accounts.states.messages.switching.off');
  await accountsStore.switchState(accountValue, state, updateAccount);
}

const resetState = () => {
  updateAccountLoader.value = false;
  stateText.value = '';
  authMethod.value = null;
}

const checkQr = async () => {
  if (account.value?.step?.value === 2.3) {
    await accountsStore.getQrCode(account.value)
  } else if (account.value?.step?.value === 2.2 && !account.value?.qr_code) {
    await accountsStore.getQrCode(account.value)
  }
}

const sendCode = async () => {
  if(account.value)
    await accountsStore.solveChallenge(account.value, code.value)
}

const sendTwoFactorAuth = async () => {
  if(!account.value || !code.value)
    return

  updateAccountLoader.value = true
  await accountsStore.sendTwoFactorAuth(account.value, code.value)
  await accountsStore.getInfo(account.value)
  updateAccountLoader.value = false
  showQrDialog.value = false
}

watch(() => accountsStore.getAccount(account.value)?.step?.value, (newValue, oldValue) => {
  if (newValue === 5)
    showQrDialog.value = false
}, { deep: true })
</script>

<template>
  <VDialog
    v-if="account"
    v-model="showQrDialog"
    max-width="380"
    @after-leave=""
    @after-enter="account?.qr_code?checkQr():refreshQr()"
  >
    <template #activator="{ props: activatorProps }">
      <VBtn
        v-if="account.step?.value === 2.1"
        v-bind="activatorProps"
        prepend-icon="streamline:qr-code"
      >
        {{ $t('accounts.telegram.method.solve_challenge') }}
      </VBtn>
      <VBtn
        v-if="account.additional.config.services.authMethod === 'qr' && account.step?.value === 2.2"
        v-bind="activatorProps"
        prepend-icon="streamline:qr-code"
      >
        {{ $t('accounts.telegram.method.show_qr_code') }}
      </VBtn>
      <VBtn
        v-if="account.additional.config.services.authMethod === 'qr' && account.step?.value === 2.3"
        v-bind="activatorProps"
        prepend-icon="streamline:qr-code"
      >
        {{ $t('accounts.telegram.method.reset_qr_code') }}
      </VBtn>
      <VBtn
        v-else-if="account.additional.config.services.authMethod === 'code' && account.step?.value === 2.22"
        v-bind="activatorProps"
        variant="flat"
        prepend-icon="hugeicons:binary-code"
      >
        {{ $t('accounts.telegram.method.show_code') }}
      </VBtn>
    </template>
    <template #default="{ isActive }">
      <VCard min-height="500" flat>
        <VCardItem class="px-3">
          <template #prepend>
            <VIcon icon="hugeicons:binary-code"/>
          </template>
          <template #append>
            <IconBtn @click="isActive.value = false">
              <VIcon icon="mdi-close"/>
            </IconBtn>
          </template>
          <VCardTitle>{{ $t('accounts.whatsapp.auth.code.title') }}</VCardTitle>
        </VCardItem>
        <VCardItem
          v-if="account"
          class="px-2 pb-3"
        >
          <div
            v-if="account.additional.config.services.authMethod === 'qr'"
            class="d-flex justify-center align-content-center"
          >
            <VImg
              v-if="!account.qr_code && account.step?.value !== 2.1"
              class="mx-auto"
              height="350"
              width="350"
              :lazy-src="TelegramLazy"
              :src="undefined"
            >
              <template #placeholder>
                <div class="d-flex align-center justify-center fill-height">
                  <VProgressCircular
                    color="primary"
                    indeterminate
                  />
                </div>
              </template>
            </VImg>
            <QRCodeVue3
              v-else-if="account.qr_code"
              :width="350"
              :height="350"
              :value="account.qr_code"
              :key="account.qr_code"
              :qr-options="{ errorCorrectionLevel: 'H' }"
              :image-options="{ hideBackgroundDots: true, imageSize: 0.3, margin: 5 }"
              :corners-square-options="{ type: 'dot', color: '#34495E' }"
              :corners-dot-options="{
                    type: undefined,
                    color: '#25a3e2'
                  }"
              :dots-options="{
                    type: 'dots',
                    color: '#25a3e2',
                    /*gradient: {
                      type: 'linear',
                      rotation: 0,
                      colorStops: [
                        { offset: 0, color: '#41B883' },
                        { offset: 1, color: '#257124' }
                      ]
                    }*/
                  }"
              :download="false"
              image="/images/telegram.png"
            />
          </div>
          <div
            v-if="account.step?.value === 2.1"
            class="d-flex justify-center align-content-center"
          >
            <VCardItem
              v-if="!showPhoneDialog && account.phone_code"
              class="mt-3 text-h1"
              style="min-height: 350px;"
            >
              <VBtn
                variant="text"
                color="info"
                size="xxl"
                @click="copyText(account.phone_code)"
              >
                {{ account.phone_code }}
              </VBtn>
            </VCardItem>
            <VCardItem
              v-if="account?.step?.value === 2.1"
              class="mt-3 w-100"
              style="min-height: 350px;"
            >
              <VTextField
                v-model="code"
                class="my-3"
                :disabled="updateAccountLoader"
                :label="$t('accounts.settings.auth_method.phone.2fa')"
              />
            </VCardItem>
            <VSheet v-else min-height="330" class="mt-3">
              <div class="d-flex align-center justify-center fill-height">
                <VProgressCircular
                  color="primary"
                  indeterminate
                />
              </div>
            </VSheet>
          </div>
        </VCardItem>
        <VListItem v-if="account">
          <VListItemSubtitle class="mb-3">{{ $t('accounts.telegram.auth_method.subtitle') }}</VListItemSubtitle>
          <VBtn
            variant="outlined"
            color="info"
            block
            base-color="success"
            :disabled="loading.refresh || updateAccountLoader"
            @click="sendCode"
            >
              {{ $t('accounts.telegram.auth_method.send_code') }}
          </VBtn>
        </VListItem>
        <VDivider class="mb-3" />
        <VCardActions>
          <VBtn :loading="loading.refresh || updateAccountLoader" variant="outlined" color="success" class="me-3" @click="refreshQr">
            {{ $t('accounts.telegram.method.refresh') }}
          </VBtn>
          <VBtn
            v-if="account?.step?.value === 2.1"
            :loading="loading.refresh || updateAccountLoader"
            variant="flat"
            color="primary"
            @click="sendTwoFactorAuth"
          >
            {{ $t('accounts.whatsapp.phone-button-proceed') }}
          </VBtn>
          <div v-else>
            <VBtn :loading="loading.confirm || updateAccountLoader" variant="flat" color="primary" @click="confirmQr">
              {{ $t('accounts.telegram.method.confirm') }}
            </VBtn>
          </div>
        </VCardActions>
      </VCard>
    </template>
  </VDialog>
</template>
