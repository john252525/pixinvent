<script setup lang="ts">
import { themeConfig } from "@themeConfig";
import { VNodeRenderer } from "@layouts/components/VNodeRenderer";
import authV1BottomShape from '@images/svg/auth-v1-bottom-shape.svg?raw'
import authV1TopShape from '@images/svg/auth-v1-top-shape.svg?raw'

definePage({
  meta: {
    layout: 'blank',
    subject: 'user',
    action: 'read',
  }
})

const route = useRoute()
const userStore = useUserStore()

const { id, hash } = route.params
const query = route.query

const emailVerified = ref(false)

onMounted(async () => {
  const token = useCookie('accessToken').value
  const queryParams = new URLSearchParams(query).toString();
  const { response, data } = await useFetch(`/verify-email/${id}/${hash}?${queryParams}`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Authorization: `Bearer ${token}`,
    },
  })
  const { message } = JSON.parse(data.value)
  if(response.value?.status === 201)
    userStore.toast.info(message)
  else if (response.value?.status === 200)
    userStore.toast.success(message)

  emailVerified.value = true

  userStore.notifications = []
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
        :class="$vuetify.display.smAndUp ? 'pa-6' : 'pa-2'"
      >
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

        <VCardTitle style="height: 120px;">
          {{ emailVerified ? $t('auth.email.verified') : $t('auth.email.not-verified') }}
        </VCardTitle>

        <VSpacer class="h-100" />

        <VBtn
          block
          to="/"
          class="my-5"
        >
          {{ $t('text.to-home') }}
        </VBtn>
      </VCard>
    </div>
  </div>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth";
</style>
