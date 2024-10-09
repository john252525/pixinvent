<script setup lang="ts">
import { getI18n } from '@/plugins/i18n'
import { vConfetti } from '@neoconfetti/vue'
import type { MaskaDetail, MaskInputOptions } from 'maska'
import { isEmpty } from '@core/utils/helpers'

const props = defineProps(['source'])
const accountsStore = useAccountsStore()
const userStore = useUserStore()

const { t } = getI18n().global

const phone = ref()

const onMaskaError = ref(true)
const maskaOptions = reactive<MaskInputOptions>({
  mask: (value: string) => value.startsWith('8') ? '8 (###) ###-##-##' : '+7 (###) ###-##-##',
  onMaska: (detail: MaskaDetail) => {
    onMaskaError.value = detail.completed
    phone.value = detail.unmasked
  },
})

const onMaska = (event: CustomEvent<MaskaDetail>) => {
  if (event.detail.completed) {
    phone.value = `7${event.detail.unmasked}`
  } else {
    phone.value = null
  }
}

const loading = ref(false)
const isActive = ref(false)

const saveAccount = async () => {
  loading.value = true
  await $api(`user/sources/${ props.source }`, {
    method: 'PUT',
    body: {
      account: { login: phone.value },
    },
    onResponseError({ response }): Promise<void> | void {
      accountsStore.getAccounts()
      loading.value = false
      isActive.value = false
      console.log(response)
    },
    async onResponse({ response }) {
      loading.value = false
      if (response._data.status === 'error')
        userStore.toast.error(t(response._data.error.message))
      else
        accountsStore.addAccount(response._data)
      isActive.value = false
    },
  })
}

const notInValidator = () => {
  if (isEmpty(phone.value)) return true;
  if (accountsStore.total === 0) return true;

  const account = accountsStore.accounts[accountsStore.source].find(account =>
    account.login === phone.value
  );

  return !account || account.login !== phone.value;
}

const clearSession = () => {
  phone.value = null
}

defineExpose({ isActive })
</script>

<template>
  <VDialog v-model="isActive" @after-leave="clearSession">
    <template #activator="{ props: activatorProps }">
      <IconBtn v-bind="activatorProps">
        <VIcon
          size="20"
          icon="tabler-plus"
        />
      </IconBtn>
    </template>

    <template #default="{ isActive }">
      <VCard
        class="mx-auto"
        flat
      >
        <VCardItem>
          <VCardTitle>
            Создание нового аккаунта
          </VCardTitle>
          <template #append>
            <IconBtn @click="isActive.value = false">
              <VIcon icon="mdi-close" />
            </IconBtn>
          </template>
        </VCardItem>

        <VCard
          max-width="500"
          height="300"
          min-width="380"
          :loading
          flat
        >
          <VCardItem>
            <VTextField
              v-maska="maskaOptions"
              :label="$t('Enter your phone number')"
              class="my-3"
              :rules="[notInValidator]"
              @maska="onMaska"
            />
          </VCardItem>

          <VCardText>
            Инструкция по добавлению аккаунта, подробно
          </VCardText>
        </VCard>

        <VDivider></VDivider>

        <VCardActions class="mt-3">
          <VBtn variant="outlined" @click="isActive.value = false">Отмена</VBtn>
          <VBtn :disabled="!phone" :loading variant="flat" @click="saveAccount">Создать</VBtn>
        </VCardActions>
      </VCard>
    </template>
  </VDialog>
</template>

<style scoped lang="scss">

</style>
