<script setup lang="ts">
import "@vuepic/vue-datepicker/dist/main.css";
import { getI18n } from '@/plugins/i18n'
import { toMoney } from '@/utils/helpers'

definePage({
  meta: {
    action: 'read',
    subject: 'user',
  },
})

// Setup
const route = useRoute()
const router = useRouter()
const { t } = getI18n().global
const paymentStatus = ref()
const paymentDate = ref()
const userId = ref()

// TODO: get statuses from API
const statuses = [
  { title: t('succeeded'), value: 'succeeded' },
  { title: t('created'), value: 'created' },
  { title: t('pending'), value: 'pending' },
  { title: t('waiting for capture'), value: 'waiting_for_capture' },
  { title: t('canceled'), value: 'canceled' },
]

// Pagination & Sorting
const page = ref(Number.parseInt(route.hash ? route.hash.match(/\d+/)[0] : '1'))
const itemsPerPage = ref(10)
const sortBy = ref()
const orderBy = ref()

const headers = ref([
  { title: t('id'), align: 'start', key: 'id' },
  { title: t('service'), align: 'start', key: 'service' },
  { title: t('status'), key: 'status', align: 'center' },
  { title: t('amount'), key: 'amount', align: 'center' },
  { title: t('income_amount'), key: 'income_amount', align: 'center' },
  { title: t('payment_method'), key: 'payment_method', align: 'center' },
  { title: t('refunded_amount'), key: 'refunded_amount', align: 'center' },
  { title: t('user'), key: 'user', align: 'center' },
  { title: t('created_at'), key: 'created_at', align: 'center' },
  { title: t('updated_at'), key: 'updated_at', align: 'end' },
])
// Update data table options
const updateOptions = (options: any) => {
  page.value = options.page
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
  itemsPerPage.value = options.itemsPerPage
}

// 👉 Fetching users
const {
  data: transactionsData,
  execute: fetchPayments,
  isFetching: loading,
} = await useApi<any>(createUrl('/admin/transactions', {
  query: {
    paymentStatus,
    itemsPerPage,
    paymentDate,
    userId,
    page,
    sortBy,
    orderBy,
  },
}))

const transactions = computed(() => transactionsData.value.data)
const total = computed(() => transactionsData.value.meta.total)

const search = ref(null);

// Функция для отправки запроса на сервер
const {
  data: usersData,
  execute: fetchUsers,
  isFetching: loadingUsers,
} = await useApi<any>(createUrl('/admin/users/search', {
  query: {
    search,
    relation: 'transactions',
  },
}))
const users = computed(() => usersData.value.data)

watch(page, (pageValue: number) => {
  router.push({ hash: `#${pageValue}` })
})
</script>

<template>
  <VCard :title="$t('Payments List')">
    <VRow class="mx-2 mb-2">
      <VCol cols="12" sm="4">
        <VSelect
          v-model="paymentStatus"
          :label="$t('Payment Status')"
          :items="statuses"
          clearable
        />
      </VCol>
      <VCol cols="12" sm="4">
        <AppDatePicker v-model="paymentDate" />
      </VCol>
      <VCol cols="12" sm="4">
        <VAutocomplete
          v-model="userId"
          :label="$t('user')"
          :items="users"
          item-title="name"
          item-value="id"
          clearable
          :loading="loadingUsers"
          @update:search="search = $event"
        />
      </VCol>

      <VCol cols="12">
        <VDataTableServer
          :items-per-page="itemsPerPage"
          :items-per-page-options="[10, 20, 50, 100]"
          :page
          :headers
          :items="transactions"
          :items-length="total"
          :loading="loading"
          class="text-no-wrap users-table"
          @update:options="updateOptions"
        >
          <template #item.status="{ item }">{{ $t(item.status) }}</template>
          <template #item.amount="{ item }">{{ toMoney(item.amount) }}</template>
          <template #item.income_amount="{ item }">{{ toMoney(item.income_amount) }}</template>
          <template #item.created_at="{ item }">{{ $dayjs(item.created_at).format('L LTS') }}</template>
          <template #item.updated_at="{ item }">{{ $dayjs(item.updated_at).format('L LTS') }}</template>
          <template #item.payment_method="{ item }">{{ item.payment_method?.title }}</template>
          <template #item.refunded_amount="{ item }">{{ item.refunded_amount?.value }}</template>
          <template #item.user="{ item }">{{ item.user.name }}</template>
        </VDataTableServer>
      </VCol>
    </VRow>
  </VCard>
</template>

<style lang="scss">
@use "@styles/libs/vue-datapiker/index.scss";
</style>
