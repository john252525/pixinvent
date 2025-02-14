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
    unauthenticatedOnly: true,
  },
})

const route = useRoute('verify-email')

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
  return route.query.email as string || 'No email provided';
});

onMounted(() => console.log(email.value))
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

        <VCardText>
          <h4 class="text-h4 mb-1">
            {{ $t('auth.verify-email-title') }}
          </h4>
          <p class="text-body-1 mb-0">
            {{ $t('auth.password-reset-link') }}
            <span class="font-weight-medium text-high-emphasis">{{ email }}</span>
            {{ $t('auth.password-reset-follow') }}
          </p>

          <VBtn
            block
            to="/"
            class="my-5"
          >
            {{ $t('auth.back-to-login') }}
          </VBtn>

          <div class="d-flex align-center justify-center">
            <span class="me-1">{{ $t("auth.didnt-get-email") }} </span>
            <RouterLink :to="{ name: 'forgot-password' }">
              {{ $t('auth.resend') }}
            </RouterLink>
          </div>
        </VCardText>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
