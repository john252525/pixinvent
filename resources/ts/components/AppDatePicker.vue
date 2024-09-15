<script setup lang="ts">
import "@vuepic/vue-datepicker/dist/main.css";
import VueDatePicker from '@vuepic/vue-datepicker'
import { ru, enGB } from 'date-fns/locale'
import { getI18n } from '@/plugins/i18n'
import { cookieRef } from '@layouts/stores/config'
import { themeConfig } from '@themeConfig'

const paymentDate = defineModel()
const paymentDates = ref()

const { t } = getI18n().global
const locale = cookieRef("language", themeConfig.app.i18n.defaultLocale).value

const stringifyDates = (date: any) => {
  paymentDate.value = date ? JSON.stringify(date) : null;
}
</script>

<template>
  <VueDatePicker
    v-model="paymentDates"
    :multi-calendars="{ static: false }"
    :format-locale="locale === 'ru' ? ru : enGB"
    format="PP"
    range
    utc
    :placeholder="$t('Select payment creation date range')"
    :dark="!!$vuetify.theme.current.dark"
    :select-text="$t('Select')"
    :cancel-text="$t('Cancel')"
    :enable-time-picker="false"
    :teleport="true"
    @update:model-value="stringifyDates"
  />
</template>

<style lang="scss">
@use "@styles/libs/vue-datapiker/index.scss";
</style>
