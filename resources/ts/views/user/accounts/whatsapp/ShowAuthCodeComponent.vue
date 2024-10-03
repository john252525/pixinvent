<script lang="ts" setup>
import QRCodeVue3 from "qrcode-vue3"
import WhatsappLazy from '@images/misc/whatsapp-lazy.png'
import { getI18n } from '@/plugins/i18n'
import { useAccountsStore } from '@/stores/AccountsStore'
import { useUserStore } from '@/stores/UserStore'
import { useClipboard } from '@vueuse/core'
import type { MaskaDetail, MaskInputOptions } from 'maska'
import type { AccountClient } from '@/stores/types/accounts'

const account = defineModel<AccountClient>('account')

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
const phone = ref<number|string|undefined>(account.value?.additional.config.authPhone)
const phonePlaceholder = ref<number|string|undefined>(account.value?.additional.config.authPhone)
const stateText = ref()
const authMethod = ref<null|string>(null)

const onMaskaError = ref(true)

const maskaOptions = reactive<MaskInputOptions>({
  mask: (value: string) => value.startsWith('8') ? '8 (###) ###-##-##' : '+# (###) ###-##-##',
  eager: true,
  onMaska: (detail: MaskaDetail) => {
    onMaskaError.value = detail.completed
    phone.value = detail.unmasked
  },
})

const onMaska = (event: CustomEvent<MaskaDetail>) => {
  if (event.detail.completed) {
    phone.value = event.detail.unmasked.startsWith('7') ? event.detail.unmasked : `7${event.detail.unmasked}`
  } else {
    phone.value = undefined
  }
}

const refreshQr = async () => {
  if (!account.value)
    return

  loading.refresh = true
  accountsStore.accounts[accountsStore.source][accountsStore.getAccountIndex(account.value)].qr_code = undefined
  await accountsStore.getQrCode(account.value)
  loading.refresh = false
}
const confirmQr = async () => {
  if (!account.value)
    return

  loading.confirm = true
  await accountsStore.getQrCode(account.value)
  loading.confirm = false
}

const cancelChangeAuth = (value: string) => {
  phone.value = undefined
  authMethod.value = null
  showPhoneDialog.value = false
  showQrDialog.value = false
  if (account.value)
    account.value.additional.config.services.authMethod = value === 'code' ? 'qr' : 'code'
}

const updateAuthMethod = async (value: string) => {
  if (!account.value)
    return

  if (value === 'code' && !showPhoneDialog.value) {
    showPhoneDialog.value = value === 'code';
    return;
  }

  showPhoneDialog.value = false;
  updateAccountLoader.value = true;

  await switchAccountState(account.value, false, true);
  await accountsStore.switchAuth(account.value, phone.value, value);
  await switchAccountState(account.value, true);

  resetState();
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
</script>

<template>
  <VDialog
    v-if="account"
    max-width="380"
    @after-leave=""
    @after-enter="account?.qr_code?undefined:refreshQr()"
  >
    <template #activator="{ props: activatorProps }">
      <VBtn
        v-if="account.additional.config.services.authMethod === 'qr' && account.step?.value === 2.2"
        v-bind="activatorProps"
        prepend-icon="streamline:qr-code"
      >
        Показать QR код
      </VBtn>
      <VBtn
        v-else-if="account.additional.config.services.authMethod === 'code' && account.step?.value === 2.22"
        v-bind="activatorProps"
        variant="flat"
        prepend-icon="hugeicons:binary-code"
      >
        Показать код авторизации
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
              v-if="account.step?.value === 2.2 && !account.qr_code"
              class="mx-auto"
              height="350"
              width="350"
              :lazy-src="WhatsappLazy"
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
              v-else-if="account.step?.value === 2.2 && account.qr_code"
              :width="350"
              :height="350"
              :value="account.qr_code"
              :key="account.qr_code"
              :qr-options="{ errorCorrectionLevel: 'H' }"
              :image-options="{ hideBackgroundDots: true, imageSize: 0.3, margin: 5 }"
              :corners-square-options="{ type: 'dot', color: '#34495E' }"
              :corners-dot-options="{
                    type: undefined,
                    color: '#41B883'
                  }"
              :dots-options="{
                    type: 'dots',
                    color: '#25d366',
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
              image="/images/whatsapp.png"
            />
          </div>
          <div
            v-else-if="account.additional.config.services.authMethod === 'code'"
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
              v-else-if="showPhoneDialog"
              class="mt-3 w-100"
              style="min-height: 350px;"
            >
              <VTextField
                v-model="phonePlaceholder"
                class="my-3"
                v-maska="maskaOptions"
                :label="$t('accounts.settings.auth_method.phone.label')"
                inputmode="numeric"
                @maska="onMaska"
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
        <VCardTitle>{{ $t('accounts.settings.auth_method.title') }}</VCardTitle>
        <VListItem v-if="account">
          <VRadioGroup
            v-model="account.additional.config.services.authMethod"
            :hint="stateText"
            persistent-hint
            @update:model-value="updateAuthMethod"
          >
            <VRadio :label="$t('accounts.settings.auth_method.label.qr')" value="qr"></VRadio>
            <VRadio :label="$t('accounts.settings.auth_method.label.code')" value="code"></VRadio>
          </VRadioGroup>
        </VListItem>
        <VDivider class="mb-3" />
        <VCardActions>
          <VBtn
            v-if="showPhoneDialog"
            :loading="loading.refresh"
            variant="flat"
            color="info"
            @click="updateAuthMethod('code')"
          >
            {{ $t('accounts.whatsapp.phone-button-proceed') }}
          </VBtn>
          <div v-else>
            <VBtn :loading="loading.refresh" variant="outlined" color="success" @click="refreshQr">
              {{ $t('Refresh') }}
            </VBtn>
            <VBtn :loading="loading.confirm" variant="flat" color="primary" @click="confirmQr">
              {{ $t('Confirm') }}
            </VBtn>
          </div>
        </VCardActions>
      </VCard>
    </template>
  </VDialog>
</template>
