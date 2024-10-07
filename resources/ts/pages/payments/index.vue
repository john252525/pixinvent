<script setup lang="ts">
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

const paymentStatus = ref('succeeded')
const paymentDate = ref()

// TODO: get statuses from API
const statuses = [
  { title: t('payments.transactions.succeeded'), value: 'succeeded' },
  { title: t('payments.transactions.created'), value: 'created' },
  { title: t('payments.transactions.pending'), value: 'pending' },
  { title: t('payments.transactions.waiting'), value: 'waiting_for_capture' },
  { title: t('payments.transactions.canceled'), value: 'canceled' },
]

// Pagination & Sorting
const page = ref(Number.parseInt(route.hash ? route.hash.match(/\d+/)[0] : '1'))
const itemsPerPage = ref(10)
const sortBy = ref()
const orderBy = ref()

const headers = ref([
  { title: t('service'),
    align: 'start',
    key: 'service',
  },
  { title: t('payments.transactions.status'), key: 'status', align: 'center' },
  { title: t('payments.transactions.amount'), key: 'amount', align: 'start' },
  { title: t('payments.transactions.created_at'), key: 'created_at', align: 'end' },
  { title: t('payments.transactions.updated_at'), key: 'updated_at', align: 'end' },
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
} = await useApi<any>(createUrl('/user/transactions', {
  query: {
    paymentStatus,
    itemsPerPage,
    paymentDate,
    page,
    sortBy,
    orderBy,
  },
}))

const transactions = computed(() => transactionsData.value.data)
const total = computed(() => transactionsData.value.meta.total)

watch(page, (pageValue: number) => {
  router.push({ hash: `#${pageValue}` })
})
</script>

<template>
  <VCard :title="$t('payments.transactions.payments-list')">
    <VRow class="mx-2 mb-2">
      <VCol cols="12" sm="4">
        <VSelect
          v-model="paymentStatus"
          :label="$t('payments.transactions.payment-status')"
          :items="statuses"
          clearable
        />
      </VCol>
      <VCol cols="12" sm="8">
        <AppDatePicker v-model="paymentDate" />
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
          <template #item.created_at="{ item }">{{ $dayjs(item.created_at).format('L LTS') }}</template>
          <template #item.updated_at="{ item }">{{ $dayjs(item.updated_at).format('L LTS') }}</template>
        </VDataTableServer>
      </VCol>
    </VRow>
  </VCard>
</template>

<style scoped lang="scss">
  //
</style>
