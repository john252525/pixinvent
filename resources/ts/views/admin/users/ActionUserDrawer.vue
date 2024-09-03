<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import type { VForm } from 'vuetify/components/VForm'
import type { UserProperties } from '@/views/admin/users/types'

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
  (e: 'fetchUsers'): void
}

interface Props {
  isDrawerOpen: boolean
  roles: any
  userData: UserProperties
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const user = ref<UserProperties>({
  id: undefined,
  name: '',
  email: '',
  password: undefined,
  roles: undefined,
})

const errors = ref({
  name: undefined,
  email: undefined,
  password: undefined,
  roles: undefined,
})

const isFormValid = ref(false)
const refForm = ref<VForm>()
const lockPassword = ref(true)
const isPasswordVisible = ref(false)

const resetForm = () => {
  nextTick(() => {
    refForm.value?.resetValidation()
  })
}

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  resetForm()
}

const onSubmit = () => {
  refForm.value?.validate().then(async ({ valid }) => {
    if (valid) {
      await $api(`/admin/users/${user.value?.id ? user.value?.id : ''}`, {
        method: user.value?.id ? 'POST' : 'PUT',
        body: user.value,
        onResponseError({ response }) {
          errors.value = response._data.errors
        },
      })

      emit('update:isDrawerOpen', false)
      emit('fetchUsers')
      resetForm()
    }
  })
}

const handleDrawerModelValueUpdate = (val: boolean) => {
  resetForm()
}

const lockPasswordProceed = () => {
  lockPassword.value = !lockPassword.value
}

const setEmailVerifiedAt = () => {
  user.value.email_verified_at = !user.value.email_verified_at ? new Date().toISOString() : null
}

watch(() => props.userData, (data: UserProperties) => {
  refForm.value?.resetValidation()
  user.value = data
})

const isDrawerOpens = ref(false)

watch(isDrawerOpens, (isOpen: boolean) => {
  console.log(isOpen)
})
</script>

<template>
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
      title="Cоздание пользователя"
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
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- 👉 Name -->
              <VCol cols="12">
                <VTextField
                  v-model="user.name"
                  :rules="[requiredValidator]"
                  :error-messages="errors.name"
                  label="Имя пользователя"
                  @update:model-value="errors.name = undefined"
                />
              </VCol>

              <!-- 👉 Username -->
              <VCol cols="12">
                <VTextField
                  v-model="user.email"
                  :rules="[requiredValidator, emailValidator]"
                  :error-messages="errors.email"
                  label="Электронная почта"
                  @update:model-value="errors.email = undefined"
                >
                  <template #append-inner>
                    <VIcon
                      :icon="`mdi-${user.email_verified_at ? 'check' : 'close'}-circle`"
                      :color="user.email_verified_at ? 'success' : 'error'"
                      size="24"
                      @click="setEmailVerifiedAt"
                    />
                  </template>
                </VTextField>
              </VCol>

              <VCol
                v-if="user.id !== undefined"
                cols="12"
                class="d-flex justify-space-between align-center"
              >
                <VTextField
                  v-model="user.password"
                  :error-messages="errors.password"
                  label="Пароль"
                  :rules="[requiredValidatorIf(user.password, lockPassword)]"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :disabled="lockPassword"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  @update:model-value="errors.password = undefined"
                />
                <IconBtn
                  icon
                  class="cursor-pointer"
                  @click="lockPasswordProceed"
                >
                  <VIcon
                    color="secondary"
                    :icon="lockPassword ? 'mdi-checkbox-blank-outline' : 'mdi-checkbox-outline'"
                    size="24"
                  />
                </IconBtn>
              </VCol>

              <VCol
                v-else
                cols="12"
                class="d-flex justify-space-between align-center"
              >
                <VTextField
                  v-model="user.password"
                  :error-messages="errors.password"
                  label="Пароль"
                  :rules="[requiredValidator]"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  @update:model-value="errors.password = undefined"
                />
              </VCol>

              <VDivider class="my-3" />

              <VCardTitle class="text-start">
                Права доступа
              </VCardTitle>

              <!-- 👉 Role -->
              <VCol cols="12">
                <VSelect
                  v-model="user.roles"
                  label="Роли"
                  :rules="[requiredValidator]"
                  :error-messages="errors.roles"
                  return-object
                  :items="props.roles"
                  multiple
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
                  Отмена
                </VBtn>
                <VBtn type="submit">
                  Сохранить
                </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </PerfectScrollbar>
  </VNavigationDrawer>
</template>

<style lang="scss">
  //
</style>
