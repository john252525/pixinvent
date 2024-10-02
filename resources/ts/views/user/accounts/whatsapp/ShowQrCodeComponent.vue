<script lang="ts" setup>
import QRCodeVue3 from "qrcode-vue3"
import WhatsappLazy from '@images/misc/whatsapp-lazy.png'
import { getI18n } from '@/plugins/i18n'
import type { AccountClient } from '@/stores/types/accounts'
import { useAccountsStore } from '@/stores/AccountsStore'

const props = withDefaults(defineProps<{
  account: AccountClient,
}>(), {})

const accountsStore = useAccountsStore()

const t = getI18n().global

const { pause, resume, isActive: isTimerActive } = useIntervalFn(async () => {

}, import.meta.env.VITE_QR_TIMEOUT * 1000)

const { ready, start, stop } = useTimeout(2000, { controls: true })

const loading = reactive({
  refresh: false,
  confirm: false,
})

const refreshQr = async () => {
  loading.refresh = true
  accountsStore.accounts[accountsStore.source][accountsStore.getAccountIndex(props.account)].qr_code = undefined
  await accountsStore.getQrCode(props.account)
  loading.refresh = false
}
const confirmQr = async () => {
  loading.confirm = true
  await accountsStore.getQrCode(props.account)
  loading.confirm = false
}
</script>

<template>
  <VDialog
    max-width="380"
    @after-leave=""
    @after-enter="account?.qr_code?undefined:refreshQr()"
  >
    <template #activator="{ props: activatorProps }">
      <IconBtn
        v-if="!props.account.qr_code"
        v-bind="activatorProps"
      >
        <VTooltip :text="$t('Click on this button to get WhatsApp qr code')">
          <template #activator="{ props }">
            <VIcon v-bind="props" icon="streamline:qr-code" />
          </template>
        </VTooltip>
      </IconBtn>
      <IconBtn
        v-else
        v-bind="activatorProps"
      >
        <VTooltip :text="$t('If the code is outdated, click this button to update it')">
          <template v-slot:activator="{ props }">
            <VIcon color="light-green" v-bind="props" icon="streamline:qr-code" />
          </template>
        </VTooltip>
      </IconBtn>
    </template>
    <template #default="{ isActive }">
      <VCard min-height="500" flat>
        <VCardItem class="px-3">
          <template #prepend>
            <VIcon icon="mdi-qrcode"/>
          </template>
          <template #append>
            <IconBtn @click="isActive.value = false">
              <VIcon icon="mdi-close"/>
            </IconBtn>
          </template>
          <VCardTitle>Whatsapp {{ $t('QR code') }}</VCardTitle>
        </VCardItem>
        <VCardItem class="px-2 pb-3">
          <div class="text-center">
            <VImg
              v-if="!props.account.qr_code"
              class="mx-auto"
              height="380"
              width="380"
              :lazy-src="WhatsappLazy"
              :src="undefined"
            >
              <template v-slot:placeholder>
                <div class="d-flex align-center justify-center fill-height">
                  <VProgressCircular
                    color="primary"
                    indeterminate
                  />
                </div>
              </template>
            </VImg>
            <QRCodeVue3
              v-else
              :width="350"
              :height="350"
              :value="props.account.qr_code"
              :key="props.account.qr_code"
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
        </VCardItem>
        <VDivider/>
        <VCardActions>
          <VBtn :loading="loading.refresh" variant="outlined" color="success" @click="refreshQr">
            {{ $t('Refresh') }}
          </VBtn>
          <VBtn :loading="loading.confirm" variant="flat" color="primary" @click="confirmQr">
            {{ $t('Confirm') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </template>
  </VDialog>
</template>
