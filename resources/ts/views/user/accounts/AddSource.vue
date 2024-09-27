<script setup lang="ts">
import { getI18n } from '@/plugins/i18n'
import { vConfetti } from '@neoconfetti/vue'

const props = defineProps(['source'])
const emits = defineEmits(['on-added'])

const { t } = getI18n().global

const login = ref()
const loading = ref(false)
const isActive = ref(false)

const saveAccount = async () => {
  loading.value = true
  await $api(`user/sources/${ props.source }`, {
    method: 'PUT',
    body: {
      login: login.value,
    },
    onResponseError(error): Promise<void> | void {
      loading.value = false
      console.log(error)
    },
    onResponse({ response }) {
      loading.value = false
      if (response.status === 201) {
        emits('on-added', login.value)
      }
    },
  })
}

const clearSession = () => {
  login.value = null
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
              v-model="login"
              :label="$t('accLogin')"
              class="my-3"
            />
          </VCardItem>

          <VCardText>
            Инструкция по добавлению аккаунта, подробно
          </VCardText>
        </VCard>

        <VDivider></VDivider>

        <VCardActions class="mt-3">
          <VBtn variant="outlined" @click="isActive.value = false">Отмена</VBtn>
          <VBtn variant="flat" @click="saveAccount">Создать</VBtn>
        </VCardActions>
      </VCard>
    </template>
  </VDialog>
</template>

<style scoped lang="scss">

</style>
