<script setup lang="ts">
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'
import { VNodeRenderer } from '@layouts/components/VNodeRenderer'
import { themeConfig } from '@themeConfig'
import type { ThemeSwitcherTheme } from '@layouts/types'
import LangSwitcherI18n from '@core/components/I18n.vue'

definePage({
  meta: {
    layout: 'blank',
    subject: 'user',
    action: 'read'
  },
})

const route = useRoute('verify-email')
const userStore = useUserStore()

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

const email = computed(() => {
  return userStore.userData.email as string || 'No email provided';
});

const sendVerificationEmail = async () => {
  const result = await $api('/user/auth/verify-email', { method: 'POST' })
  userStore.toast.success(result.message)
}

onMounted(async () => {
  await sendVerificationEmail()
})

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
        height="500"
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-2'"
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
                  {{ $t(themeConfig.app.title) }}
                </h1>
              </div>
            </RouterLink>
          </VCardTitle>
        </VCardItem>

        <VCardText style="height: 120px;">
          <h4 class="text-h4 mb-10">
            {{ $t('auth.verify-email-title') }}️
          </h4>
          <p class="text-body-1">
            {{ $t('auth.account-activation-link') }}
            <span class="font-weight-medium text-high-emphasis">{{ email }}</span>
            {{ $t('auth.password-reset-follow') }}
          </p>

          <VSpacer class="h-100" />

          <VBtn
            block
            to="/"
            class="my-5"
          >
            {{ $t('auth.skip-for-now') }}
          </VBtn>

          <div class="d-flex align-center justify-center">
            <span class="me-1">{{ $t("auth.didnt-get-email") }} </span>
            <VBtn variant="text" @click="sendVerificationEmail">
              {{ $t('auth.resend') }}
            </VBtn>
          </div>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
