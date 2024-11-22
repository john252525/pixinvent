<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import type { VForm } from 'vuetify/components/VForm'
import type { UserProperties } from '@/views/admin/users/types'
import {MailingProperties} from "@/views/user/mailing/types";
import {useMailingStore} from "@/stores/MailingStore";

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
}

interface Props {
  isDrawerOpen: boolean,
  hours: {
    text: string;
    value: number;
  },
  mailingData: MailingProperties
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const mailing = ref<MailingProperties>({
  id: undefined,
  days: '',
  time_from: '',
  time_to: '',
  delay_from: '',
  delay_to: '',
  uniq: '',
  exist: '',
  random: '',
})

const errors = ref({
  days: undefined,
  time_from: undefined,
  time_to: undefined,
  delay_from: undefined,
  delay_to: undefined,
  uniq: undefined,
  exist: undefined,
  random: undefined,
})

const isFormValid = ref(false)
const refForm = ref<VForm>()
const isPasswordVisible = ref(false)

const mailingStore = useMailingStore();

const resetForm = () => {
  nextTick(() => {
    refForm.value?.resetValidation()
    errors.value = '';
  })
}

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  document.body.style = '';
  resetForm()
}

const onSubmit = (mailing) => {
  if (typeof mailing.days === 'string') {
    mailing.days = mailing.days.trim().replace(/^,|,$/g, "").split(',')
  }

  refForm.value?.validate().then(async ({ valid }) => {
    if (valid) {
      await $api(`/user/mailing/edit`, {
        method: 'POST',
        body: mailing,
        onResponseError({ response }) {
          errors.value = response._data.errors
          console.log(response)
        },
      })

      emit('update:isDrawerOpen', false)
      mailingStore.getMailings()
      resetForm()
    }
  })
}

const handleDrawerModelValueUpdate = (val: boolean) => {
  resetForm()
}

watch(() => props.mailingData, (data) => {
  refForm.value?.resetValidation()

  const { options, ...otherProps } = data;
  const {
    days,
    uniq,
    exist,
    random,
    hours: { min: time_from, max: time_to },
    delay: { min: delay_from, max: delay_to },
  } = options;

  mailing.value = {
    ...otherProps,
    time_from,
    time_to,
    delay_from,
    delay_to,
    days,
    uniq,
    exist,
    random,
  }
})

const isDrawerOpens = ref(false)

watch(isDrawerOpens, (isOpen: boolean) => {
  console.log(isOpen)
})
</script>

<template>
  <Teleport to="body">
    <VNavigationDrawer
      temporary
      :width="400"
      location="end"
      persistent
      class="scrollable-content"
      :model-value="props.isDrawerOpen"
      @update:model-value="handleDrawerModelValueUpdate"
    >
      <!-- 👉 Title -->
      <AppDrawerHeaderSection
        :title="$t('mailings.settings.title')"
        icon="mdi-account-edit"
        @cancel="closeNavigationDrawer"
      />

      <VDivider />

      <PerfectScrollbar :options="{ wheelPropagation: false }">
        <VCard flat>
          <VCardText>
            <!-- 👉 Form -->
            <VForm
              ref="refForm"
              v-model="isFormValid"
              @submit.prevent="onSubmit(mailing)"
            >
              <VRow v-if="mailing.id !== undefined">
                <VInput
                  v-model="mailing.id"
                  type="hidden"
                />
                <!-- 👉 Name -->
                <VCol cols="12">
                  <VTextField
                    v-model="mailing.days"
                    :error-messages="errors.days"
                    :label="$t('mailings.inputs.days')"
                    @update:model-value="errors.days = undefined"
                  />
                </VCol>

              <VCol cols="12">
                <VTextField
                  v-model="mailing.time_from"
                  :error-messages="errors.time_from"
                  :label="$t('mailings.inputs.min-time')"
                  @update:model-value="errors.time_from = undefined"
                />
              </VCol>

                <VCol cols="12">
                  <VTextField
                    v-model="mailing.time_to"
                    :error-messages="errors.time_to"
                    :label="$t('mailings.inputs.max-time')"
                    @update:model-value="errors.time_to = undefined"
                  />
                </VCol>

                <VCol cols="12">
                  <VTextField
                    v-model="mailing.delay_from"
                    :error-messages="errors.delay_from"
                    :label="$t('mailings.inputs.min-delay')"
                    @update:model-value="errors.delay_from = undefined"
                  />
                </VCol>

                <VCol cols="12">
                  <VTextField
                    v-model="mailing.delay_to"
                    :error-messages="errors.delay_to"
                    :label="$t('mailings.inputs.max-delay')"
                    @update:model-value="errors.delay_to = undefined"
                  />
                </VCol>

                <VCol cols="12">
                  <VSelect
                    v-model="mailing.uniq"
                    :items="[{title: $t('On'), value: true}, {title: $t('Off'), value: false}]"
                    :error-messages="errors.uniq"
                    :label="$t('mailings.inputs.delete-duplicates')"
                    @update:model-value="errors.uniq = undefined"
                  />
                </VCol>

                <VCol cols="12">
                  <VSelect
                    v-model="mailing.exist"
                    :error-messages="errors.exist"
                    :items="[{title: $t('On'), value: true}, {title: $t('Off'), value: false}]"
                    :label="$t('mailings.inputs.exist')"
                    @update:model-value="errors.exist = undefined"
                  />
                </VCol>

                <VCol cols="12">
                  <VSelect
                    v-model="mailing.random"
                    :items="[{title: $t('On'), value: true}, {title: $t('Off'), value: false}]"
                    :error-messages="errors.random"
                    :label="$t('mailings.inputs.random')"
                    @update:model-value="errors.random = undefined"
                  />
                </VCol>

                <VCol cols="12">
                  <VTextField
                    v-model="mailing.cascade"
                    :error-messages="errors.cascade"
                    :label="$t('mailings.inputs.cascade')"
                    @update:model-value="errors.cascade = undefined"
                  />
                </VCol>

                <!-- 👉 Submit and Cancel -->
                <VCol
                  cols="12"
                  class="d-flex justify-space-between"
                >
                  <VBtn
                    variant="outlined"
                    color="secondary"
                    class="me-3"
                    @click="closeNavigationDrawer"
                  >
                    {{ $t('Cancel') }}
                  </VBtn>
                  <VBtn type="submit">
                    {{ $t('Save') }}
                  </VBtn>
                </VCol>
              </VRow>
            </VForm>
          </VCardText>
        </VCard>
      </PerfectScrollbar>
    </VNavigationDrawer>
  </Teleport>
</template>

<style lang="scss">
//
</style>
