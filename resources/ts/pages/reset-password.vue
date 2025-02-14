<script setup lang="ts">
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import { router } from '@/plugins/1.router'

definePage({
  meta: {
    layout: 'blank',
    public: true,
  },
})

const route = useRoute()

const form = ref({
  password: '',
  password_confirmation: '',
})

const userStore = useUserStore()
const refRestPassword = ref()
const isPasswordVisible = ref(false)
const isConfirmPasswordVisible = ref(false)
const loading = ref(false)
const errors = ref({
  password: undefined,
  password_confirmation: undefined,
})

const resetPassword = () => {
  refRestPassword.value.validate().then(async ({ valid: isValid }: { valid: boolean }) => {
    if(isValid) {
      await $api('/user/auth/set-new-password', {
        method: 'POST',
        body: {
          email: route.query.email,
          token: route.query.token,
          password: form.value.password,
          password_confirmation: form.value.password_confirmation,
        },
        onResponseError({ response }) {
          errors.value = response?._data.password ?? response?._data.message
        },
        onResponse({ response}) {
          console.log(router)
          if(response.status === 200) {
            errors.value = response?._data.errors
            userStore.toast.success(response?._data.message)
            router.push({ name: 'login' })
          } else {
            userStore.toast.error(response?._data.message)
          }
        }
      })
    }
  })
}
</script>

<template>
  <div class="auth-wrapper d-flex align-center justify-center pa-4">
    <div class="position-relative my-sm-16">
      <!-- 👉 Top shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1TopShape })"
        class="text-primary auth-v1-top-shape d-none d-sm-block"
      />

      <!-- 👉 Bottom shape -->
      <VNodeRenderer
        :nodes="h('div', { innerHTML: authV1BottomShape })"
        class="text-primary auth-v1-bottom-shape d-none d-sm-block"
      />

      <!-- 👉 Auth Card -->
      <VCard
        class="auth-card"
        max-width="460"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-2'"
      >
        <VCardItem class="justify-center">
          <VCardTitle>
            <RouterLink to="/">
              <div class="app-logo">
                <VNodeRenderer :nodes="themeConfig.app.logo" />
                <h1 class="app-logo-title">
                  {{ themeConfig.app.title }}
                </h1>
              </div>
            </RouterLink>
          </VCardTitle>
        </VCardItem>

        <VCardText>
          <h4 class="text-h4 mb-1">
            {{ $t('auth.reset-password-title') }}
          </h4>
          <p class="mb-0">
            {{ $t('auth.reset-password-message') }}
          </p>
        </VCardText>

        <VCardText>
          <VForm
            ref="refRestPassword"
            @submit.prevent="resetPassword"
          >
            <VRow>
              <!-- password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  autofocus
                  :label="$t('auth.new-password')"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  :rules="[requiredValidator, lengthValidator(form.password, 8), confirmedValidator(form.password, form.password_confirmation)]"
                  :error-messages="errors.password"
                  @update:model-value="errors.password = errors.password_confirmation = undefined"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />
              </VCol>

              <!-- Confirm Password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.password_confirmation"
                  :label="$t('auth.confirm-password')"
                  :type="isConfirmPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isConfirmPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  :rules="[requiredValidator, confirmedValidator(form.password, form.password_confirmation)]"
                  :error-messages="errors.password_confirmation"
                  @click:append-inner="isConfirmPasswordVisible = !isConfirmPasswordVisible"
                />
              </VCol>

              <!-- reset password -->
              <VCol cols="12">
                <VBtn
                  block
                  type="submit"
                >
                  {{ $t('auth.set-new-password') }}
                </VBtn>
              </VCol>

              <!-- back to login -->
              <VCol cols="12">
                <RouterLink
                  class="d-flex align-center justify-center"
                  :to="{ name: 'login' }"
                >
                  <VIcon
                    icon="tabler-chevron-left"
                    size="20"
                    class="me-1 flip-in-rtl"
                  />
                  <span>{{ $t('auth.back-to-login') }}</span>
                </RouterLink>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
