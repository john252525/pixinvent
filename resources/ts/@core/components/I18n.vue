<script setup lang="ts">
import type { I18nLanguage } from '@layouts/types'

interface Props {
  languages: I18nLanguage[]
  location?: any
}

const props = withDefaults(defineProps<Props>(), {
  location: 'bottom end',
})

const { locale } = useI18n({ useScope: 'global' })

const setLocale = (lang: string) => {
  locale.value = lang
  localStorage.setItem('locale', lang)
}
</script>

<template>
  <IconBtn>
    <VIcon :icon="`flagpack:${props.languages.find(lang => lang.i18nLang === locale)?.flag}`" />

    <!-- Menu -->
    <VMenu
      activator="parent"
      :location="props.location"
      offset="12px"
      width="175"
    >
      <!-- List -->
      <VList
        :selected="[locale]"
        color="primary"
      >
        <!-- List item -->
        <VListItem
          v-for="lang in props.languages"
          :key="lang.i18nLang"
          :value="lang.i18nLang"
          :prepend-icon="`flagpack:${lang.flag}`"
          @click="setLocale(lang.i18nLang)"
        >
          <!-- Language label -->
          <VListItemTitle>
            {{ lang.label }}
          </VListItemTitle>
        </VListItem>
      </VList>
    </VMenu>
  </IconBtn>
</template>
