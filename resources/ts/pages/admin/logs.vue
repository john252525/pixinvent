<script setup lang="ts">
import { useApi } from '@/composables/useApi'
import { createUrl } from '@core/composable/createUrl'
import { getI18n } from '@/plugins/i18n'

definePage({
  meta: {
    action: 'read',
    subject: 'admin',
    title: 'admin.logs.page.title',
  },
})

const userStore = useUserStore()
const { t } = getI18n().global

// Data table options
const search = ref('')
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const updateOptions = (options: any) => {
  page.value = options.page
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
  itemsPerPage.value = options.itemsPerPage
}

const headers = [
  { title: '#', align: 'start', key: 'id', width: 20 },
  { title: t('admin.logs.created_at'), key: 'created_at', align: 'center', sortable: false },
  { title: t('admin.logs.type'), key: 'type', align: 'start', sortable: false },
  { title: t('admin.logs.user'), key: 'user', align: 'start', sortable: false },
  { title: t('admin.logs.errors'), key: 'errors', align: 'start', sortable: false },
  { title: t('admin.logs.actions'), key: 'actions', align: 'end', sortable: false },
] as any[]

const {
  data: logsData,
  execute: fetchLogs,
  isFetching: loading,
} = await useApi<any>(createUrl('admin/logs', {
  query: {
    page,
    search,
    itemsPerPage,
    sortBy,
    orderBy,
  },
}))

const logs = computed(() => logsData.value.data)
const itemsLength = computed(() => logsData.value.meta.total)
const deleteItem = (item: any) => {
  $api(`admin/logs/${item.id}`, { method: 'DELETE' })
    .then((response: any) => {
      userStore.toast.success(response.message)
      fetchLogs()
  })
}
</script>

<template>
  <VDataTableServer
    :items-per-page="itemsPerPage"
    :items-per-page-options="[10, 20, 50, 100]"
    :page="page"
    :items="logs"
    :headers="headers"
    :items-length="itemsLength"
    :loading
    class="text-no-wrap users-table"
    @update:options="updateOptions"
  >
    <template #top>
      <VListItem>
        <template #title>
          <VCardTitle>{{ $t('admin.logs.table.title') }}</VCardTitle>
        </template>
        <template #append>
          <IconBtn @click="fetchLogs">
            <VIcon icon="mdi-refresh" />
          </IconBtn>
        </template>
      </VListItem>
    </template>
    <template #item.type="{ item }">
      {{ $t(item.type) }}
    </template>
    <template #item.created_at="{ item }">
      {{ $dayjs(item.created_at).format('LLL') }}
    </template>
    <template #item.user="{ item }">
      {{ item.user?.email }}
    </template>
    <template #item.actions="{ item }">
      <IconBtn @click="deleteItem(item)">
        <VIcon icon="mdi-delete" />
      </IconBtn>
    </template>
  </VDataTableServer>
</template>

<style scoped lang="scss">

</style>
