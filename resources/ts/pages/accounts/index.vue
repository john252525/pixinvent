<script setup lang="ts">
import AddSource from '@/views/user/accounts/AddSource.vue'
import { getI18n } from '@/plugins/i18n'

const { t } = getI18n().global
const page = ref(1)
const source = ref('whatsapp')
const itemsPerPage = ref(10)
const sortBy = ref('id')
const orderBy = ref('desc')

const updateOptions = (options: any) => {
  page.value = options.page
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const {
  data: sourcesData,
  execute: fetchSources,
  isFetching: loading,
} = await useApi<any>(createUrl('/user/sources', {
  query: {
    source,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const headers = ref([
  { title: t('accLogin'), key: 'login', align: 'start', sortable: false },
  { title: t('Step'), key: 'step', align: 'start', sortable: false },
  { title: '', key:'separator', align:'center', sortable: false, width: '100%' },
  { title: t('Actions'), key: 'actions', align: 'end', sortable: false },
])

const clients = computed(() => sourcesData.value.clients)
const total = computed(() => sourcesData.value.clients.length)

const deleteAccount = async (login: string) => {
  const response = await $api(`user/sources/${source.value}`, {
    method: 'DELETE',
    body: {
      login: login,
    },
  })
  fetchSources()
}
</script>

<template>
  <VCard>
    <VDataTableServer
      :items="clients"
      :items-length="total"
      :loading="loading"
      :headers
      class="text-no-wrap users-table"
      @update:options="updateOptions"
    >
      <template #top>
        <VRow class="ma-1">
          <VCol cols="12" class="d-flex justify-space-between">
            <VCardTitle>Accounts</VCardTitle>
            <div class="d-flex gap-3">
              <AddSource />
              <VBtn color="success" icon="mdi-refresh" rounded @click="fetchSources" />
            </div>
          </VCol>
        </VRow>
        <VRow class="ma-1">
          <VCol cols="12" sm="4">
            <VSelect
              v-model="source"
              :items="['telegram', 'whatsapp']"
              :label="$t('Select source')"
            />
          </VCol>
        </VRow>
        <VDivider />
      </template>
      <template #item.step="{ item }">
        <span :class="item.step ? 'cursor-help': 'cursor-not-allowed'">
          {{ item.step ? item.step.value : $t('offline') }}
        <VTooltip
          v-if="item.step"
          activator="parent"
          location="top"
        >
          {{ item.step.message }}
        </VTooltip>
        </span>
      </template>

      <template #item.actions="{ item }">
        <VIcon
          start
          size="small"
        >
          mdi-pencil
        </VIcon>
        <VIcon
          size="small"
          @click="deleteAccount(item.login)"
        >
          mdi-delete
        </VIcon>
      </template>
    </VDataTableServer>
  </VCard>
</template>

<style scoped lang="scss">

</style>
