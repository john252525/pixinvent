<script setup lang="ts">
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import LangSwitcherI18n from '@core/components/I18n.vue'
import type { ThemeSwitcherTheme } from '@layouts/types'
import { VForm } from 'vuetify/components/VForm'

definePage({
  meta: {
    layout: 'blank',
    unauthenticatedOnly: true,
  },
})

const themes: ThemeSwitcherTheme[] = [
  {
    name: 'light',
    icon: 'tabler-sun-high',
  },
  {
    name: 'dark',
    icon: 'tabler-moon-stars',
  },
  {
    name: 'system',
    icon: 'tabler-device-desktop-analytics',
  },
]

const isPasswordVisible = ref(false)
const isCPasswordVisible = ref(false)
const refVForm = ref<VForm>()

const form = ref({
  name: 'admin',
  email: 'admin@example.com',
  password: '12344321',
  password_confirmation: '12344321',
  privacyPolicies: true,
})

const errors = ref<Record<string, string | undefined>>({
  name: undefined,
  email: undefined,
  password: undefined,
  password_confirmation: undefined,
  privacyPolicies: undefined,
})

const route = useRoute()
const router = useRouter()

const ability = useAbility()

const register = async () => {
  try {
    const res = await $api('/user/auth/register', {
      method: 'POST',
      body: form.value,
      onResponseError({ response }) {
        errors.value = response._data.errors
      },
    })

    const { accessToken, userData, userAbilityRules } = res

    ability.update(userAbilityRules)

    useCookie('userAbilityRules').value = userAbilityRules
    useCookie('userData').value = userData
    useCookie('accessToken').value = accessToken

    // Redirect to `to` query if exist or redirect to index route
    // ❗ nextTick is required to wait for DOM updates and later redirect
    await nextTick(() => {
      router.replace(route.query.to ? String(route.query.to) : '/')
    })
  }
  catch (err) {
    console.error(err)
  }
}

const onSubmit = () => {
  refVForm.value?.validate()
    .then(({ valid: isValid }: any) => {
      if (isValid)
        register()
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

      <!-- 👉 Auth card -->
      <VCard
        class="auth-card"
        max-width="460"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-0'"
      >
        <ThemeSwitcher :themes="themes" class="float-end" />
        <LangSwitcherI18n
          v-if="themeConfig.app.i18n.enable && themeConfig.app.i18n.langConfig?.length"
          :languages="themeConfig.app.i18n.langConfig"
          class="float-end"
        />
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
            {{ $t('auth.register.title') }}
          </h4>
          <p class="mb-0">
            {{ $t('auth.register.message') }}
          </p>
        </VCardText>

        <VCardText>
          <VForm
            ref="refVForm"
            @submit.prevent="onSubmit"
          >
            <VRow>
              <!-- Username -->
              <VCol cols="12">
                <VTextField
                  v-model="form.name"
                  :rules="[requiredValidator]"
                  :error-messages="errors.name"
                  autofocus
                  :label="$t('auth.name')"
                  @update:model-value="errors.name = undefined"
                />
              </VCol>
              <!-- email -->
              <VCol cols="12">
                <VTextField
                  v-model="form.email"
                  :rules="[requiredValidator, emailValidator]"
                  :error-messages="errors.email"
                  :label="$t('auth.email')"
                  type="email"
                  @update:model-value="errors.email = undefined"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <VTextField
                  v-model="form.password"
                  :rules="[requiredValidator]"
                  :error-messages="errors.password"
                  :label="$t('auth.password')"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :append-inner-icon="isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                  @update:model-value="errors.password = undefined"
                />
              </VCol>

                <!-- Confirm password -->
                <VCol cols="12">
                  <VTextField
                    v-model="form.password_confirmation"
                    :rules="[requiredValidator]"
                    :label="$t('auth.confirm-password')"
                    :type="isCPasswordVisible ? 'text' : 'password'"
                    :append-inner-icon="isCPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'"
                    @click:append-inner="isCPasswordVisible = !isCPasswordVisible"
                  />

                  <div class="d-flex align-center my-6">
                  <VCheckbox
                    id="privacy-policy"
                    v-model="form.privacyPolicies"
                    inline
                  />
                  <VLabel
                    for="privacy-policy"
                    style="opacity: 1;"
                  >
                    <span class="me-1 text-high-emphasis">{{ $t('auth.agree-to') }}</span>
                    <a
                      href="javascript:void(0)"
                      class="text-primary"
                    >{{ $t('auth.privacy-policy') }}</a>
                  </VLabel>
                </div>

                <VBtn
                  block
                  type="submit"
                >
                  {{ $t('auth.sign-up') }}
                </VBtn>
              </VCol>

              <!-- login instead -->
              <VCol
                cols="12"
                class="text-center text-base"
              >
                <span>{{ $t('auth.already-have-account') }}</span>
                <RouterLink
                  class="text-primary ms-1"
                  :to="{ name: 'login' }"
                >
                  {{ $t('auth.sign-in') }}
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
