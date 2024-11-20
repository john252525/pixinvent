<script setup lang="ts">

import { useMailingStore } from '@/stores/MailingStore'
import type {VForm} from "vuetify/lib/components/VForm";

const loading = ref(false)
const isActive = ref(false)

const mailing = ref({
  name: '',
  ph_col: 'A',
  days: '1,2,3,4,5',
  file_base: '',
  file_attach: '',
  text: '',
  time_from: '10:00',
  time_to: '17:00',
  timezone: 3,
  delay_from: 900,
  delay_to: 2700,
  uniq: 1,
  exist: 1,
  random: 0,
  cascade: 'whatsapp'
})

const mailingStore = useMailingStore();

const errors = ref({
  name: undefined,
  base: undefined,
  ph_col: undefined,
  days: undefined,
  file_base: undefined,
  file_attach: undefined,
  text: undefined,
  time_from: undefined,
  time_to: undefined,
  timezone: undefined,
  delay_from: undefined,
  delay_to: undefined,
})

const isFormValid = ref(false)
const refForm = ref<VForm>()

const resetForm = () => {
  nextTick(() => {
    refForm.value?.resetValidation()
    errors.value = ''
  })
}

const resetFormValues = () => {
  // if (refForm.value) {
  //   refForm.value.reset();
  // }
  refForm.value = {
    name: '',
    file_base: '',
    file_attach: '',
    text: '',
  };
}

const saveMailing = async () => {
  refForm.value?.validate().then(async ({ valid }) => {
    if (valid) {
      console.log(mailing.value)
      const formData = new FormData();

      if (typeof mailing.value.days === 'string') {
        mailing.value.days = mailing.value.days.trim().replace(/^,|,$/g, "")
      }

      Object.keys(mailing.value).forEach(key => {
        formData.append(key, mailing.value[key]);
      })

      loading.value = true

      await $api(`user/mailing/create`, {
        method: 'POST',
        body: formData,
        onResponse({ response }) {
          loading.value = false
        },
        onResponseError({ response }): Promise<void> | void {
          errors.value = response._data.errors
        },
      })

      mailingStore.getMailings();
      resetForm()
      isActive.value = false
    }
  })
}

</script>

<template>
  <VDialog v-model="isActive">
    <template #activator="{ props: activatorProps }">
      <IconBtn @click="resetFormValues()" v-bind="activatorProps">
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
            {{ $t('mailings.create.title') }}
          </VCardTitle>
          <template #append>
            <IconBtn @click="isActive.value = false; resetForm()">
              <VIcon icon="mdi-close" />
            </IconBtn>
          </template>
        </VCardItem>

        <VCard
          max-width="700"
          height="400"
          min-width="380"
          width="550"
          :loading
          flat
          class="overflow-auto"
        >
          <VForm
            ref="refForm"
            v-model="isFormValid"
            @submit.prevent="saveMailing(mailing)"
          >
            <VCardItem>
              <VTextField
                v-model="mailing.name"
                :label="$t('mailings.inputs.name')"
                :error-messages="errors.name"
                class="my-3"
                @update:model-value="errors.name = undefined"
              />
              <VTextarea
                v-model="mailing.base"
                :label="$t('mailings.inputs.contact-list')"
                :error-messages="errors.name"
                class="my-3"
                @update:model-value="errors.base = undefined"
              />
              <VFileInput
                v-model="mailing.file_base"
                :label="$t('mailings.inputs.contact-list-by-file')"
                :error-messages="errors.file_base"
                accept="xlsx, xls, csv, txt"
                class="my-3"
                @update:model-value="errors.file_base = undefined"
              />
              <VTextarea
                v-model="mailing.text"
                :label="$t('mailings.inputs.text')"
                :error-messages="errors.text"
                class="my-3"
                @update:model-value="errors.text = undefined"
              />
              <VTextField
                v-model="mailing.ph_col"
                :label="$t('mailings.inputs.ph-col')"
                :error-messages="errors.ph_col"
                class="my-3"
                @update:model-value="errors.ph_col = undefined"
              />
              <VFileInput
                v-model="mailing.file_attach"
                :label="$t('mailings.inputs.attach')"
                :error-messages="errors.file_attach"
                accept="jpeg, jpg, gif, png, pdf, doc, docx, xls, xlsx"
                class="my-3"
                @update:model-value="errors.file_attach = undefined"
              />
              <VTextField
                v-model="mailing.days"
                :label="$t('mailings.inputs.days')"
                :error-messages="errors.days"
                class="my-3"
                @update:model-value="errors.days = undefined"
              />
              <VTextField
                v-model="mailing.time_from"
                :label="$t('mailings.inputs.min-time')"
                :error-messages="errors.time_from"
                class="my-3"
                @update:model-value="errors.time_from = undefined"
              />
              <VTextField
                v-model="mailing.time_to"
                :label="$t('mailings.inputs.max-time')"
                :error-messages="errors.time_to"
                class="my-3"
                @update:model-value="errors.time_to = undefined"
              />
              <VTextField
                v-model="mailing.timezone"
                :label="$t('mailings.inputs.timezone')"
                :error-messages="errors.timezone"
                class="my-3"
                @update:model-value="errors.timezone = undefined"
              />
              <VTextField
                v-model="mailing.delay_from"
                :label="$t('mailings.inputs.min-delay')"
                :error-messages="errors.delay_from"
                class="my-3"
                @update:model-value="errors.delay_from = undefined"
              />
              <VTextField
                v-model="mailing.delay_to"
                :label="$t('mailings.inputs.max-delay')"
                :error-messages="errors.delay_to"
                class="my-3"
                @update:model-value="errors.delay_to = undefined"
              />
              <VSelect
                v-model="mailing.uniq"
                :label="$t('mailings.inputs.delete-duplicates')"
                :items="[{title: $t('On'), value: 1}, {title: $t('Off'), value: 0}]"
                :error-messages="errors.uniq"
                class="my-3"
                @update:model-value="errors.uniq = undefined"
              />
              <VSelect
                v-model="mailing.exist"
                :label="$t('mailings.inputs.exist')"
                :items="[{title: $t('On'), value: 1}, {title: $t('Off'), value: 0}]"
                :error-messages="errors.exist"
                class="my-3"
                @update:model-value="errors.exist = undefined"
              />
              <VSelect
                v-model="mailing.exist"
                :label="$t('mailings.inputs.random')"
                :items="[{title: $t('On'), value: 1}, {title: $t('Off'), value: 0}]"
                :error-messages="errors.random"
                class="my-3"
                @update:model-value="errors.random = undefined"
              />
              <VTextField
                v-model="mailing.cascade"
                :label="$t('mailings.inputs.cascade')"
                :error-messages="errors.cascade"
                class="my-3"
                @update:model-value="errors.cascade = undefined"
              />
            </VCardItem>
          </VForm>
        </VCard>

        <VDivider></VDivider>

        <VCardActions class="mt-3">
          <VBtn variant="outlined" @click="isActive.value = false">Отмена</VBtn>
          <VBtn :loading variant="flat" @click="saveMailing">Создать</VBtn>
        </VCardActions>
      </VCard>
    </template>
  </VDialog>
</template>

<style scoped lang="scss">

</style>
