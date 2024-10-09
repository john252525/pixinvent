<script setup lang="ts">
import ActionUserDrawer from '@/views/admin/users/ActionUserDrawer.vue'
import type { UserProperties } from '@/views/admin/users/types'
import { getI18n } from '@/plugins/i18n'
import { toMoney } from '@/utils/helpers'

definePage({
  meta: {
    action: 'read',
    subject: 'admin',
  },
})

// Setup
const route = useRoute()
const router = useRouter()
const { t } = getI18n().global

// Filters
const search = ref()
const roleId = ref()

// Pagination & Sorting
const page = ref(Number.parseInt(route.hash ? route.hash.match(/\d+/)[0] : '1'))
const itemsPerPage = ref(10)
const sortBy = ref()
const orderBy = ref()

//Action User Drawer
const isActionUserDrawerVisible = ref(false)

// Update data table options
const updateOptions = (options: any) => {
  page.value = options.page
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
  itemsPerPage.value = options.itemsPerPage
}

// 👉 Fetching users
const {
  data: usersData,
  execute: fetchUsers,
  isFetching: loading,
} = await useApi<any>(createUrl('/admin/users/', {
  query: {
    search,
    roleId,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const users = computed(() => usersData.value.data)
const roles = computed(() => usersData.value.roles)
const total = computed(() => usersData.value.meta.total)

// Headers
const headers = [
  { title: t('admin.users.id'), key: 'id', width: '20px', align: 'center' },
  { title: t('admin.users.name'), key: 'name' },
  { title: t('admin.users.balance'), key: 'balance', sortable: false },
  { title: t('admin.users.roles'), key: 'roles', sortable: false },
  { title: t('admin.users.created_at'), key: 'created_at' },
  { title: t('admin.users.actions'), key: 'actions', sortable: false, align: 'end' },
] as const

const user = ref<UserProperties>({
  name: '',
  email: '',
} as UserProperties)

const actionUser = (editedUser: any = {}) => {
  user.value = editedUser
  if (Object.keys(editedUser).length) {
    user.value.roles = editedUser.roles.map((r: any) => ({
      title: r.name,
      value: r.id,
    }))
  }
  isActionUserDrawerVisible.value = true
}

const deleteUser = async (deletedUser: any) => {
  await $api(`/admin/users/${deletedUser.id}`, {
    method: 'DELETE',
  })

  await fetchUsers()
}
</script>

<template>
  <VCard :title="$t('admin.users.title')">
    <VCardItem class="py-0">
      <VRow>
        <VCol cols="12" sm="3">
          <VSelect
            v-model="roleId"
            :items="roles"
            :label="$t('admin.users.select-role')"
            class="my-2"
            clearable
          />
        </VCol>
        <VCol
          cols="12"
          sm="9"
          class="d-flex align-end gap-1"
        >
          <VTextField
            v-model="search"
            prepend-inner-icon="tabler-search"
            :label="$t('admin.users.search')"
            class="my-2"
            clearable
          />
          <VBtn
            class="my-2"
            icon
            rounded
            color="success"
            @click="fetchUsers"
          >
            <VIcon>mdi-refresh</VIcon>
          </VBtn>
          <VBtn
            class="my-2"
            icon
            rounded
            @click="actionUser()"
          >
            <VIcon>mdi-plus</VIcon>
          </VBtn>
        </VCol>
      </VRow>
    </VCardItem>
    <VCardItem class="pt-0">
      <VRow no-gutters>
        <VCol cols="12">
          <VDataTableServer
            :items-per-page="itemsPerPage"
            :items-per-page-options="[10, 20, 50, 100]"
            :page="page"
            :items="users"
            :items-length="total"
            :headers="headers"
            :loading="loading"
            class="text-no-wrap users-table"
            @update:options="updateOptions"
          >
            <template #item.name="{ item }">
              <VListItem
                :title="item.name"
                :subtitle="item.email"
              >
                <template #prepend>
                  <VAvatar>
                    <VIcon
                      icon="mdi-user-circle"
                      size="36"
                    />
                  </VAvatar>
                </template>
              </VListItem>
            </template>

            <template #item.balance="{ item }">
              {{ toMoney(item.balance) }}
            </template>

            <template #item.roles="{ item }">
              <VChipGroup v-if="item.roles?.length">
                <VChip
                  v-for="(role, id) in item.roles"
                  :key="id"
                >
                  {{ role.name }}
                </VChip>
              </VChipGroup>
              <VCardText v-else>{{ $t('admin.users.not-assignet') }}</VCardText>
            </template>

            <template #item.created_at="{ item }">
              <VListItem
                class="pa-0"
                density="default"
              >
                {{ $t('admin.users.created') }}: {{ $dayjs(item.created_at).fromNow() }}
              </VListItem>
              <VListItem
                class="pa-0"
                density="default"
              >
                {{ $t('admin.users.updated') }}: {{ $dayjs(item.updated_at).fromNow() }}
              </VListItem>
            </template>

            <template #item.actions="{ item }">
              <VIcon
                start
                size="small"
                @click="actionUser(item)"
              >
                mdi-pencil
              </VIcon>
              <VIcon
                size="small"
                @click="deleteUser(item)"
              >
                mdi-delete
              </VIcon>
            </template>
          </VDataTableServer>
        </VCol>
      </VRow>
    </VCardItem>

  </VCard>
  <!-- 👉 Add New User -->
  <ActionUserDrawer
    v-model:isDrawerOpen="isActionUserDrawerVisible"
    v-model:user-data="user"
    v-model:roles="roles"
    @fetch-users="fetchUsers"
  />
</template>

<style scoped lang="scss">
  //
</style>
