<script setup lang="ts">
import type { CustomInputContent } from '@core/types'
import { getI18n } from '@/plugins/i18n'

const step = ref(1)
const { t } = getI18n().global

const selectSourceContent: CustomInputContent[] = [
  {
    title: t('Whatsapp'),
    desc: t('For freelancers who work with multiple clients'),
    value: 'whatsapp',
    icon: { icon: 'logos:whatsapp-icon', size: '28' },
  },
  {
    title: t('Telegram'),
    desc: t('Team plan for free upto 15 seats'),
    value: 'telegram',
    icon: { icon: 'logos:telegram', size: '28' },
  },
]

const selectSourceTypeContent: CustomInputContent[] = [
  {
    title: t('Link activation'),
    desc: t('For freelancers who work with multiple clients'),
    value: 'link',
    icon: { icon: 'mdi-link-variant', size: '28' },
  },
  {
    title: t('QR code activation'),
    desc: t('Team plan for free upto 15 seats'),
    value: 'qrcode',
    icon: { icon: 'mdi-qrcode', size: '28' },
  },
]

const selectedSource = ref('whatsapp')
const selectedSourceType = ref('link')
</script>

<template>
  <VDialog max-width="500">
    <template #activator="{ props: activatorProps }">
      <VBtn
        v-bind="activatorProps"
        color="primary"
        icon="mdi-plus"
        rounded
      />
    </template>

    <template #default="{ isActive }">
      <VCard
        class="mx-auto"
        max-width="450"
      >
        <VCardTitle class="text-h6 font-weight-regular justify-space-between">
          <VAvatar
            color="primary"
            size="24"
            v-text="step"
          ></VAvatar>
        </VCardTitle>

        <VWindow v-model="step" class="ma-3">
          <VWindowItem :value="1">
            <CustomRadiosWithIcon
              v-model:selected-radio="selectedSource"
              :radio-content="selectSourceContent"
              :grid-column="{ cols: '6' }"
            />
          </VWindowItem>

          <VWindowItem :value="2">
            <CustomRadiosWithIcon
              v-model:selected-radio="selectedSourceType"
              :radio-content="selectSourceTypeContent"
              :grid-column="{ cols: '6' }"
            />
          </VWindowItem>

          <VWindowItem :value="3">
            <div class="pa-4 text-center">
              <VCardText>
                <v-text-field
                  label="Enter your phone number"
                ></v-text-field>
                <span class="text-caption text-grey-darken-1">
                  Please enter a phone your {{ selectedSourceType }} account
                </span>
              </VCardText>
              <VImg
                class="mb-4"
                height="128"
                src="https://raw.githubusercontent.com/scholtz/qrcode-vue3/master/src/assets/telegram_example_new.png"
                contain
              ></VImg>
            </div>
          </VWindowItem>
        </VWindow>

        <VDivider></VDivider>

        <VCardActions class="mt-3">
          <VBtn
            v-if="step > 1"
            variant="text"
            @click="step--"
          >
            Back
          </VBtn>
          <VSpacer></VSpacer>
          <VBtn
            v-if="step < 3"
            color="primary"
            variant="flat"
            @click="step++"
          >
            Next
          </VBtn>
        </VCardActions>
      </VCard>
    </template>
  </VDialog>
</template>

<style scoped lang="scss">

</style>
