<script setup lang="ts">
import safeBoxWithGoldenCoin from '@images/misc/3d-safe-box-with-golden-dollar-coins.png'
import spaceRocket from '@images/misc/3d-space-rocket-with-smoke.png'
import dollarCoinPiggyBank from '@images/misc/dollar-coins-flying-pink-piggy-bank.png'
import { vConfetti } from '@neoconfetti/vue'

interface Pricing {
  title?: string
  xs?: number | string
  sm?: number | string
  md?: string | number
  lg?: string | number
  xl?: string | number
}

const props = defineProps<Pricing>()

const annualMonthlyPlanPriceToggler = ref(true)

const pricingPlans = [
  {
    name: 'Basic',
    tagLine: 'A simple start for everyone',
    logo: dollarCoinPiggyBank,
    monthlyPrice: 0,
    yearlyPrice: 0,
    isPopular: false,
    current: true,
    features: [
      '100 responses a month',
      'Unlimited forms and surveys',
      'Unlimited fields',
      'Basic form creation tools',
      'Up to 2 subdomains',
    ],
  },
  {
    name: 'Standard',
    tagLine: 'For small to medium businesses',
    logo: safeBoxWithGoldenCoin,
    monthlyPrice: 150,
    yearlyPrice: 1200,
    isPopular: true,
    current: false,
    features: [
      'Unlimited responses',
      'Unlimited forms and surveys',
      'Instagram profile page',
      'Google Docs integration',
      'Custom “Thank you” page',
    ],
  },
  {
    name: 'Enterprise',
    tagLine: 'Solution for big organizations',
    logo: spaceRocket,
    monthlyPrice: 350,
    yearlyPrice: 3600,
    isPopular: false,
    current: false,
    features: [
      'PayPal payments',
      'Logic Jumps',
      'File upload with 5GB storage',
      'Custom domain support',
      'Stripe integration',
    ],
  },
]

const seedConfetti = ref(false)
const onSubmit = async (amount: Number) => {
  const response = await $api('/payments/yookassa', {
    method: 'POST',
    body: { amount },
  })

  // Инициализация виджета. Все параметры обязательные, кроме объекта customization.
  const checkout = new window.YooMoneyCheckoutWidget({
    confirmation_token: response.confirmation.confirmation_token, // Токен, который перед проведением оплаты нужно получить от ЮKassa
    // return_url: window.location.href, // Ссылка на страницу завершения оплаты

    //Настройка виджета
    customization: {
      //Настройка цветовой схемы, минимум один параметр, значения цветов в HEX
      colors: {
        /*background: '#2F3349', // Цвет фона платежной формы
         control_primary: '#00BF96', // Цвет кнопки Заплатить и других акцентных элементов
         control_primary_content: '#FFFFFF', // Цвет текста кнопки Заплатить
         control_secondary: '#366093', // Цвет неакцентных элементов интерфейса
         border: '#244166', // Цвет границ и разделителей
         text: '#DBDCE0' // Цвет текста*/
      },

      //Настройка способа отображения
      modal: true
    },
    error_callback: function (error: any) {
      console.log(error)
    }
  })

  checkout.on('complete', (res: any) => {
    //Код, который нужно выполнить после оплаты.
    seedConfetti.value = true

    setTimeout(() => {
      seedConfetti.value = false;
    }, 10000)

    //Удаление инициализированного виджета
    checkout.destroy();
  });

  //Отображение платежной формы в контейнере
  checkout.render('payment-form');
}
</script>

<template>
  <!-- 👉 Title and subtitle -->
  <div class="text-center">
    <h3 class="text-h3 pricing-title mb-2">
      {{ props.title ? props.title : $t('Pricing Plans') }}
    </h3>
    <p class="mb-0">
      All plans include 40+ advanced tools and features to boost your product.
    </p>
    <p class="mb-2">
      Choose the best plan to fit your needs.
    </p>
  </div>

  <!-- 👉 Annual and monthly price toggler -->

  <div class="d-flex font-weight-medium text-body-1 align-center justify-center mx-auto mt-12 mb-6">
    <VLabel
      for="pricing-plan-toggle"
      class="me-3"
    >
      {{ $t('Monthly') }}
    </VLabel>

    <div class="position-relative">
      <VSwitch
        id="pricing-plan-toggle"
        v-model="annualMonthlyPlanPriceToggler"
      >
        <template #label>
          <div class="text-body-1 font-weight-medium">
            {{ $t('Annually') }}
          </div>
        </template>
      </VSwitch>

      <div class="save-upto-chip position-absolute align-center d-none d-md-flex gap-1">
        <VIcon
          icon="tabler-corner-left-down"
          size="24"
          class="flip-in-rtl mt-2 text-disabled"
        />
        <div
          v-if="seedConfetti"
          v-confetti
        />
        <VChip
          label
          color="primary"
          size="small"
        >
          {{ $t('Save up to 10%') }}
        </VChip>
      </div>
    </div>
  </div>

  <!-- SECTION pricing plans -->
  <VRow>

    <VCol
      v-for="plan in pricingPlans"
      :key="plan.logo"
      v-bind="props"
      cols="12"
    >
      <!-- 👉  Card -->
      <VCard
        flat
        border
        :class="plan.isPopular ? 'border-primary border-opacity-100' : ''"
      >
        <VCardText
          style="block-size: 3.75rem;"
          class="text-end"
        >
          <!-- 👉 Popular -->
          <VChip
            v-show="plan.isPopular"
            label
            color="primary"
            size="small"
          >
            {{ $t('Popular') }}
          </VChip>
        </VCardText>

        <!-- 👉 Plan logo -->
        <VCardText>
          <VImg
            :height="120"
            :width="120"
            :src="plan.logo"
            class="mx-auto mb-5"
          />

          <!-- 👉 Plan name -->
          <h4 class="text-h4 mb-1 text-center">
            {{ plan.name }}
          </h4>
          <p class="mb-0 text-body-1 text-center">
            {{ plan.tagLine }}
          </p>

          <!-- 👉 Plan price  -->

          <div class="position-relative">
            <div class="d-flex justify-center pt-5 pb-10">
              <div class="text-body-1 align-self-start font-weight-medium">
                {{ $t('$') }}
              </div>
              <h1 class="text-h1 font-weight-medium text-primary">
                {{ annualMonthlyPlanPriceToggler ? Math.floor(Number(plan.yearlyPrice) / 12) : plan.monthlyPrice }}
              </h1>
              <div class="text-body-1 font-weight-medium align-self-end">
                /{{ $t('month') }}
              </div>
            </div>

            <!-- 👉 Annual Price -->
            <span
              v-show="annualMonthlyPlanPriceToggler"
              class="annual-price-text position-absolute text-caption text-disabled pb-4"
            >
              {{ plan.yearlyPrice === 0 ? 'free' : `${$t('USD')} ${plan.yearlyPrice}/${ $t('Year') }` }}
            </span>
          </div>

          <!-- 👉 Plan features -->

          <VList class="card-list mb-4">
            <VListItem
              v-for="feature in plan.features"
              :key="feature"
            >
              <template #prepend>
                <VIcon
                  size="8"
                  icon="tabler-circle-filled"
                  color="rgba(var(--v-theme-on-surface), var(--v-medium-emphasis-opacity))"
                />
              </template>

              <VListItemTitle class="text-body-1">
                {{ feature }}
              </VListItemTitle>
            </VListItem>
          </VList>

          <!-- 👉 Plan actions -->
          <VBtn
            block
            :color="plan.current ? 'success' : 'primary'"
            :variant="plan.isPopular ? 'elevated' : 'tonal'"
            :active="false"
            :disabled="plan.yearlyPrice === 0"
            @click="onSubmit(annualMonthlyPlanPriceToggler ? plan.yearlyPrice : plan.monthlyPrice)"
          >
            {{ plan.yearlyPrice === 0 ? 'Your Current Plan' : 'Upgrade' }}
          </VBtn>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
  <!-- !SECTION  -->
</template>

<style lang="scss" scoped>
.card-list {
  --v-card-list-gap: 1rem;
}

.save-upto-chip {
  inset-block-start: -2.4rem;
  inset-inline-end: -6rem;
}

.annual-price-text {
  inset-block-end: 3%;
  inset-inline-start: 50%;
  transform: translateX(-50%);
}
</style>
