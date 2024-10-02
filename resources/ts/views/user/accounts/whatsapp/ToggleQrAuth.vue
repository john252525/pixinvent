<script lang="ts" setup>
import { type MaskaDetail, type MaskInputOptions } from 'maska'
import type { AccountClient } from '@/stores/types/accounts'
import { useAccountsStore } from '@/stores/AccountsStore'

const props = defineProps<{
  account: AccountClient
}>()

const accountsStore = useAccountsStore()

const showDisableQr = ref(false)
const phone = ref()

const onMaskaError = ref(true)

const maskaOptions = reactive<MaskInputOptions>({
  mask: "+# (###) ###-##-##",
  onMaska: (detail: MaskaDetail) => {
    onMaskaError.value = detail.completed
    phone.value = detail.unmasked
  },
})
const toggleLoading = ref(false)

const toggleIcon = computed(() => {
  if (toggleLoading.value)
    return 'svg-spinners:clock'

  return props.account.additional.config.services.authMethod === 'code' ? 'tabler-qrcode-off' : 'tabler-qrcode'
})
const switchAuthPhone = async (account: AccountClient) => {
  toggleLoading.value = true
  const isToggle = await accountsStore.switchAuth(account, account.additional.config.authPhone)
  if (isToggle)
    toggleLoading.value = false
}

const switchAuthQR = async (account: AccountClient) => {
  if (!phone.value)
    return showDisableQr.value = true

  toggleLoading.value = true
  const isToggle = await accountsStore.switchAuth(account, phone.value)

  if (isToggle) {
    toggleLoading.value = false
    showDisableQr.value = false
  }
}
</script>
<template>
  <VDialog
    v-model="showDisableQr"
    max-width="320"
    persistent
    @after-leave="phone = null"
  >
    <template #activator>
      <div>
        <VListItem
          :prepend-icon="toggleIcon"
          :disabled="props.account.state"
          @click="props.account.additional.config.services.authMethod === 'qr' ? switchAuthQR(props.account) : switchAuthPhone(props.account)"
        >
          <VListItemTitle v-if="props.account.additional.config.services.authMethod === 'qr'">Авторизация по QR</VListItemTitle>
          <VListItemTitle v-else>Авторизация по коду</VListItemTitle>
        </VListItem>
        <VTooltip
          :disabled="!props.account.state"
          activator="parent"
          text="Сменить метод авторизации допускается только при выключенном статусе"
        />
      </div>
    </template>
    <template #default="{ isActive }">
      <VList
        class="py-2"
        color="primary"
        elevation="12"
        rounded="lg"
      >
        <VListItem prepend-icon="mdi:cellphonee">
          <template v-slot:prepend>
            <div class="pe-4">
              <VIcon icon="mdi:cellphone" color="primary" size="x-large"/>
            </div>
          </template>

          <VTextField
            v-maska="maskaOptions"
            label="Введите телефонный номер"
            class="my-3"
            inputmode="numeric"
          />

        </VListItem>
        <VListItemAction class="d-flex float-end gap-3 mx-3">
          <VBtn color="error" variant="text" @click="isActive.value = false">Отмена</VBtn>
          <VBtn
            :loading="toggleLoading"
            :color="onMaskaError ? 'success' : 'primary'"
            :variant="onMaskaError ? 'flat' : 'text'"
            :disabled="!onMaskaError" @click="switchAuthQR(props.account)"
          >
            Ок
          </VBtn>
        </VListItemAction>
      </VList>
    </template>
  </VDialog>
</template>
