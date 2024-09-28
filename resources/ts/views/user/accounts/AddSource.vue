<script setup lang="ts">
import { getI18n } from '@/plugins/i18n'
import { vConfetti } from '@neoconfetti/vue'
import type { MaskaDetail, MaskInputOptions } from 'maska'
import { isEmpty } from '@core/utils/helpers'

const props = defineProps(['source'])
const accountsStore = useAccountsStore()

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
    onResponse({ response }) {
      loading.value = false
      accountsStore.addAccount(response._data)
      isActive.value = false
    },
  })
}

const notInValidator = () => {
  if (isEmpty(phone.value))
    return true;

  if (phone.value.length < 10)
    return t('phone.format')

  return accountsStore.accounts[accountsStore.source].filter(account => account.login === phone.value).length === 0 || 'Такой аккаунт уже существует'
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
