<script setup lang="ts">
import QRCodeVue3 from "qrcode-vue3"
import type { CustomInputContent } from '@core/types'
import { getI18n } from '@/plugins/i18n'
import WhatsappLazy from '@images/misc/whatsapp-lazy.png'
import { vConfetti } from '@neoconfetti/vue'

const emits = defineEmits(['fetch-sources'])

const step = ref(1)
const { t } = getI18n().global
const { ready, start, stop } = useTimeout(2000, { controls: true })

const selectSourceContent: CustomInputContent[] = [
  {
    title: t('Whatsapp'),
    desc: '', // t('To add an account you will need to enter your login'),
    value: 'whatsapp',
    icon: { icon: 'logos:whatsapp-icon', size: '28' },
  },
  {
    title: t('Telegram'),
    desc: '', // t('Team plan for free upto 15 seats'),
    value: 'telegram',
    icon: { icon: 'logos:telegram', size: '28' },
  },
]

const selectSourceTypeContent: CustomInputContent[] = [
  {
    title: t('Account name'),
    desc: t('Use any name convenient for you'),
    value: 'login',
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
const setStatus = ref(true)
const loadingApi = ref(false)
const login = ref()
const loginQR = ref()
const tgCode = ref()
const url = computed(() => `user/sources/${selectedSource.value}/get-qr-code?loginQR=${loginQR.value}`)

const {
  data: qrData,
  execute: fetchQrWhatsApp,
  isFetching: loadingQr,
} = await useApi<any>(url)

const qr = computed(() => qrData?.value || false)

const { pause, resume, isActive: isTimerActive } = useIntervalFn(async () => {
  await fetchQrWhatsApp()
}, import.meta.env.VITE_QR_TIMEOUT * 1000)

const firstStep = () => {
  step.value = 2
}

const secondStep = async () => {
  loadingApi.value = true
  await $api(`user/sources/${ selectedSource.value }`, {
    method: 'PUT',
    body: {
      setStatus: setStatus.value,
      login: login.value,
    },
  })

  if (selectedSource.value === 'whatsapp') {
    await fetchQrWhatsApp().then(async () => {
      emits('fetch-sources')
      loginQR.value = login.value
      step.value = 3
      loadingApi.value = false
    })
  }
  else if (selectedSource.value === 'telegram') {
    const stepTg = await getState()
    if (stepTg === 2.25)
      step.value = 3
  }
}
const thirdStep = async () => {
  const result = await $api(`user/sources/${ selectedSource.value }/solve-challenge`, {
    query: {
      login: login.value,
      code: tgCode.value,
    },
  })
}
const finalStep = () => {
  console.log(step.value)
}

const getState = async () => {
  const result = await $api(`user/sources/${ selectedSource.value }/get-info-by-token`, {
    query: {
      login: login.value,
    },
  })
  if (!result.state)
    return getState()
  else
    return result.step.value
}

const steps = [
  firstStep,
  secondStep,
  thirdStep,
  finalStep,
]
const nextStep = () => {
  steps[step.value - 1]()
}
const previousStep = () => {
  step.value--
}

const close = (modal: any) => {
  modal.value = false
  loadingApi.value = false
  login.value = null
  loginQR.value = null
  tgCode.value = null
  step.value = 1
  selectedSource.value = 'whatsapp'

}

onMounted(() => {
  pause()
  stop()
})

watch(qr, (newQr) => {
  const isWhatsApp = selectedSource.value === 'whatsapp';

  if (isWhatsApp) {
    if (step.value === 3 && newQr.length === 0) {
      step.value = 4;
    }
    start();
  }
})

watch(step, (newStep) => {
  if (newStep === 3)
    resume()
  else
    pause()
})
</script>

<template>
  <VDialog>
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
        flat
      >
        <VCardTitle class="text-h6 font-weight-regular d-flex justify-space-between align-center">
          <VAvatar
            color="primary"
            size="24"
            v-text="step"
          >
          </VAvatar>
          <IconBtn @click="close(isActive)">
            <VIcon icon="mdi-close" />
          </IconBtn>
        </VCardTitle>

        <VCard
          max-width="500"
          height="500"
          min-width="380"
          flat
        >
          <VWindow v-model="step" class="ma-3 h-100">
            <VWindowItem :value="1">
              <CustomRadiosWithIcon
                v-model:selected-radio="selectedSource"
                :radio-content="selectSourceContent"
                :grid-column="{ cols: '6' }"
              />
              <VCardText v-if="selectedSource === 'whatsapp'">
                Подключение аккаунта WhatsApp является важным шагом для обеспечения эффективной коммуникации в
                современном мире. Для начала необходимо скачать и установить приложение на ваше мобильное устройство.
                После этого откройте приложение и следуйте инструкциям на экране, вводя свой номер телефона и
                подтверждая его через SMS или QR-код . Убедитесь, что у вас есть стабильное интернет-соединение, чтобы избежать
                проблем с активацией. После успешного подключения вы сможете обмениваться сообщениями, делать голосовые
                и видеозвонки, а также создавать группы для общения с несколькими контактами одновременно. Правильная
                настройка аккаунта гарантирует безопасность ваших данных и удобство в использовании.
              </VCardText>
              <VCardText v-else-if="selectedSource === 'telegram'">
                Подключение аккаунта Telegram — это важный шаг для пользователей, стремящихся к более эффективной
                коммуникации. Для начала необходимо загрузить приложение Telegram на ваше устройство и пройти процесс
                регистрации, указав номер телефона. После получения кода подтверждения вы сможете создать свой профиль и
                настроить его по своему усмотрению. Подключение аккаунта позволяет не только обмениваться сообщениями с
                друзьями и коллегами, но и участвовать в группах, каналах и использовать боты для автоматизации задач.
                Важно помнить о безопасности: настройте двухфакторную аутентификацию, чтобы защитить свой аккаунт от
                несанкционированного доступа.
              </VCardText>
            </VWindowItem>

            <VWindowItem :value="2">
              <VCardTitle>{{ $t('Account creation') }}</VCardTitle>
              <div class="pa-4 text-center">
                <VCardText>
                  <v-text-field
                    label="Enter your account name"
                    v-model="login"
                  ></v-text-field>
                </VCardText>
                <VCardText class="pb-0">
                  <VSwitch
                    v-model="setStatus"
                    label="Start account"
                  />

                  <span class="text-caption text-grey-darken-1">
                    {{ $t('You can start your account immediately after creation') }}
                  </span>
                </VCardText>
              </div>
            </VWindowItem>

            <VWindowItem v-if="selectedSource === 'whatsapp'" :value="3">
              <VImg
                v-if="!ready"
                class="mx-auto"
                height="390"
                width="390"
                :lazy-src="WhatsappLazy"
                src="https://bad.src/not/valid"
              >
                <template v-slot:placeholder>
                  <div class="d-flex align-center justify-center fill-height">
                    <VProgressCircular
                      color="primary"
                      indeterminate
                    />
                  </div>
                </template>
              </VImg>
              <QRCodeVue3
                v-else
                :width="390"
                :height="390"
                :value="qr"
                :key="qr"
                :qr-options="{ errorCorrectionLevel: 'H' }"
                :image-options="{ hideBackgroundDots: true, imageSize: 0.4, margin: 10 }"
                :corners-square-options="{ type: 'dot', color: '#34495E' }"
                :corners-dot-options="{
                  type: undefined,
                  color: '#41B883'
                }"
                :dots-options="{
                  type: 'dots',
                  color: '#41B883',
                  gradient: {
                    type: 'linear',
                    rotation: 0,
                    colorStops: [
                      { offset: 0, color: '#41B883' },
                      { offset: 1, color: '#257124' }
                    ]
                  }
                }"
                :download="false"
                image="/images/whatsapp.png"
              />
              <div class="pa-4 text-center w-100">
                {{ $t('Scan the QR in your WhatsApp app.') }}
              </div>
            </VWindowItem>

            <VWindowItem v-if="selectedSource === 'telegram'" :value="3">
              <VCardText>
                <VTextField
                  v-model="tgCode"
                  label="Enter your Telegram code"
                />
              </VCardText>
            </VWindowItem>

            <VWindowItem :value="4">
              <div class="mx-auto" v-if="step === 4" v-confetti />
              <div class="pa-4 text-center">
                <VCardText>
                  Congratulations! Your account is ready.
                </VCardText>
              </div>
            </VWindowItem>
          </VWindow>
        </VCard>

        <VDivider></VDivider>

        <VCardActions class="mt-3">
          <VBtn
            v-if="step > 1"
            variant="text"
            @click="previousStep"
          >
            {{ $t('Back') }}
          </VBtn>
          <VSpacer></VSpacer>
          <VBtn
            v-if="step === 3"
            color="primary"
            variant="flat"
            @click="step = 4"
            :disabled="!qr"
          >
            {{ $t('Skip') }}
          </VBtn>
          <VBtn
            v-if="step < 3"
            :loading="loadingApi"
            color="primary"
            variant="flat"
            :disabled="step === 2 && !login"
            @click="nextStep"
          >
            {{ $t('Next') }}
          </VBtn>
          <VBtn
            v-if="step === 3 && selectedSource === 'telegram'"
            :loading="false"
            color="primary"
            variant="flat"
            :disabled="!tgCode"
            @click="nextStep"
          >
            {{ $t('Next') }}
          </VBtn>
          <VBtn
            v-else-if="step === 4"
            color="primary"
            variant="flat"
            @click="close(isActive)"
          >
            {{ $t('Finish') }}
          </VBtn>
        </VCardActions>
      </VCard>
    </template>
  </VDialog>
</template>

<style scoped lang="scss">

</style>
