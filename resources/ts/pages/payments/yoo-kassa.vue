<script setup lang="ts">

definePage({
  meta: {
    action: 'read',
    subject: 'user',
  },
})

const refVForm = ref()
const amount = ref('')
const onSubmit = () => {
  refVForm.value.validate().then(async (isValid: boolean) => {
    const { confirmation_token } = await $api('/payments/yookassa', {
      method: 'POST',
      body: { amount: amount.value },
    })

    // Инициализация виджета. Все параметры обязательные, кроме объекта customization.
    const checkout = new window.YooMoneyCheckoutWidget({
      confirmation_token, // Токен, который перед проведением оплаты нужно получить от ЮKassa
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
      error_callback: function(error: any) {
        console.log(error)
      }
    })

    checkout.on('complete', (res: any) => {
      //Код, который нужно выполнить после оплаты.
      console.log(res)

      //Удаление инициализированного виджета
      checkout.destroy();
    });

    //Отображение платежной формы в контейнере
    checkout.render('payment-form');
  })
}
</script>

<template>
  <VCard
    :title="$t('Test payment')"
    height="500"
  >
    <VCardItem>
      <VForm
        ref="refVForm"
        @submit.prevent="onSubmit"
      >
        <VTextField
          v-model="amount"
          :rules="[requiredValidator, positiveIntegerValidator]"
          :label="$t('payments.transactions.amount')"
          class="py-3"
        />
        <VBtn type="submit">
          Оплатить
        </VBtn>
      </VForm>
    </VCardItem>
  </VCard>
</template>

<style scoped lang="scss">

</style>
