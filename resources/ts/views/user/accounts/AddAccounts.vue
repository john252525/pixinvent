<script setup>
import { globalI18n} from '@/plugins/i18n'

const { t } = globalI18n()
const userStore = useUserStore()

const categories = [
  { title: t('accounts.add.source.messenger'), value: 'messenger' },
  { title: t('accounts.add.source.crm'), value: 'crm' },
  { title: t('accounts.add.source.helpdesk'), value: 'helpdesk' },
]

const sources = [
  { title: t('accounts.add.source.telegram'), value: 'telegram' },
  { title: t('accounts.add.source.whatsapp'), value: 'whatsapp' },
  { title: t('accounts.add.source.sms'), value: 'sms' },
]

const types = {
  whatsapp: [
    { title: t('accounts.add.type.touch-api'), value: 'touchapi' },
    { title: t('accounts.add.type.twilio'), value: 'twilio' },
  ],
  telegram: [
    { title: t('accounts.add.type.touch-api'), value: 'touchapi' },
    { title: t('accounts.add.type.chat-api'), value: 'chat-api' },
  ],
  sms: [
    { title: t('accounts.add.type.touch-api'), value: 'touchapi' },
    { title: t('accounts.add.type.sms-ru'), value: 'sms-ru' },
  ],
}

const form = reactive({
  category: '',
  source: '',
  type: '',
  token: '',
  login: '',
})

const clearForm = () => {
  form.category = ''
  form.source = ''
  form.type = ''
  form.token = ''
  form.url = ''
  form.login = ''
}

const getDisabledStatus = computed(() => {
  if (form.category ==='messenger') {

    if (form.source === 'whatsapp') {
      if (form.type === 'twilio') {
        return form.token === '' || form.login === ''
      }
      return form.type === ''
    }

    if (form.source === 'telegram') {
      if (form.type === 'chat-api') {
        return form.token === '' || form.login === ''
      }

      return form.type === ''
    }

    if (form.source === 'sms') {
      if (form.type === 'sms-ru') {
        return form.token === '' || form.login === ''
      }

      return form.type === ''
    }

    return form.source === '' || form.type === ''
  }

  if (form.category ==='crm') {
    return form.url === '' || form.token === ''
  }

  if (form.category ==='helpdesk') {
    return form.url === ''
  }

  return form.category === ''
})

const addAccount = async (isDialogActive) => {
  const result = await $api('/user/sources/add', {
    method: 'POST',
    body: form,
    onRequestError({ response }) {
      console.log(response)
    }
  })

  userStore.toast.success(t(result.message))
  isDialogActive.value = false
}
</script>

<template>
  <v-dialog
    max-width="500"
    @after-leave="clearForm"
  >
    <template v-slot:activator="{ props: activatorProps }">
      <VBtn
        v-bind="activatorProps"
        variant="flat"
        color="primary"
        density="compact"
      >
        {{ $t('accounts.add.button.title') }}
      </VBtn>
    </template>

    <template v-slot:default="{ isActive }">
      <VCard height="500" :title="$t('accounts.add.button.title')">
        <VCardText v-if="0">
          Lorem ipsum dolor sit amet, consectetur adipiscing elit
        </VCardText>

        <!-- Categories -->
        <VCardItem>
          <VSelect
            v-model="form.category"
            :label="$t('accounts.add.categories.label')"
            :items="categories"
            class="pt-3"
            @update:model-value="form.source = ''"
          />
        </VCardItem>

        <!-- Messengers -->
        <VCardItem v-if="form.category === 'messenger'">
          <VSelect
            v-model="form.source"
            :label="$t('accounts.add.source.label')"
            :items="sources"
            class="pt-3"
            @update:model-value="form.type = ''"
          />
        </VCardItem>

        <!-- Types -->
        <VCardItem v-if="form.source">
          <VSelect
            v-if="form.source ==='telegram'"
            v-model="form.type"
            :label="$t('accounts.add.type.label')"
            :items="types.telegram"
            class="pt-3"
          />
          <VSelect
            v-if="form.source ==='whatsapp'"
            v-model="form.type"
            :label="$t('accounts.add.type.label')"
            :items="types.whatsapp"
            class="pt-3"
          />
          <VSelect
            v-if="form.source ==='sms'"
            v-model="form.type"
            :label="$t('accounts.add.type.label')"
            :items="types.sms"
            class="pt-3"
          />
        </VCardItem>

        <VCardItem v-if="form.category === 'crm' || form.category === 'helpdesk'">
          <VTextField
            v-model="form.url"
            :label="$t('accounts.add.url.label')"
            class="pt-3"
          />
        </VCardItem>

        <VCardItem v-if="form.type || form.category === 'crm' || form.category === 'helpdesk'">
          <VTextField
            v-model="form.token"
            :label="$t('accounts.add.token.label')"
            class="pt-3"
          />
        </VCardItem>

        <VCardItem v-if="form.category === 'messenger'">
          <VTextField
            v-model="form.login"
            :label="$t('accounts.add.login.label')"
            class="pt-3"
          />
        </VCardItem>

        <VSpacer />

        <VCardActions>
          <VDivider />
          <VBtn
            color="secondary"
            :text="$t('accounts.add.button.close-dialog')"
            @click="isActive.value = false"
          />
          <VBtn
            color="primary"
            :text="$t('accounts.add.button.add')"
            :disabled="getDisabledStatus"
            @click="addAccount(isActive)"
          />
        </VCardActions>
      </VCard>
    </template>
  </v-dialog>
</template>
