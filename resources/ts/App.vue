<script setup lang="ts">
import { useTheme } from 'vuetify'
import ScrollToTop from '@core/components/ScrollToTop.vue'
import initCore from '@core/initCore'
import { initConfigStore, useConfigStore } from '@core/stores/config'
import { hexToRgb } from '@core/utils/colorConverter'
import { Toaster, toast } from 'vue-sonner'
import { useUserStore } from '@/stores/UserStore'

const { global } = useTheme()

const userStore = useUserStore()
userStore.toast = toast

// ℹ️ Sync current theme with initial loader theme
initCore()
initConfigStore()

const configStore = useConfigStore()
</script>

<template>
  <VLocaleProvider :rtl="configStore.isAppRTL">
    <!-- ℹ️ This is required to set the background color of active nav link based on currently active global theme's primary -->
    <VApp :style="`--v-global-theme-primary: ${hexToRgb(global.current.value.colors.primary)}`">
      <RouterView />
      <ScrollToTop />
    </VApp>
    <Toaster
      richColors
      :theme="$vuetify.theme.current.dark ? 'dark' : 'light'"
    />
  </VLocaleProvider>
</template>
