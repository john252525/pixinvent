<script setup lang="ts">
import type { Dayjs } from 'dayjs'
import { abilitySubjects, type Subjects } from '@/plugins/casl/ability'

definePage({
  meta: {
    action: 'read',
    subject: 'admin',
  },
})

const dayjs = inject<Dayjs | any>('dayjs')
const dialog = ref(false)
const dialogColor = ref(false)
const dialogDelete = ref(false)
const roleForm = ref()

const { t } = useI18n()

// Data table options
const search = ref('')
const itemsPerPage = ref(10)
const page = ref(1)
const sortBy = ref()
const orderBy = ref()

const headers = ref([
  { title: '#', align: 'start', key: 'id', width: 20 },
  { title: t('name'), key: 'name', align: 'start', sortable: false },
  // { title: 'name', key: 'name', align: 'start', sortable: false },
  // { title: 'meta.subject', key: 'meta.subject', align: 'center' },
  { title: t('admin.roles.list.permissions'), key: 'permissions', align: 'start', sortable: false },
  { title: t('admin.roles.list.timestamps'), key: 'timestamps', align: 'start', sortable: false },
  { title: t('admin.roles.list.actions'), key: 'actions', align: 'end', sortable: false },
])

const editedIndex = ref(-1)

const errors = ref({
  name: undefined,
  permissions: undefined,
})

const itemsPerPageOptions = ref([
  { value: 3, title: '3' },
  { value: 5, title: '5' },
  { value: 10, title: '10' },
  { value: 50, title: '50' },
])

const editedItem = ref({
  name: '',
  permissions: [],
  guard_name: 'web',
})

const defaultItem = ref({
  name: '',
  permissions: [],
  guard_name: 'web',
})

function formatDate(date: Dayjs) {
  return dayjs(date).fromNow()
}

const formTitle = computed(() => {
  return editedIndex.value === -1 ? t('admin.roles.list.create') : t('admin.roles.list.edit')
})

const updateOptions = (options: any) => {
  page.value = options.page
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const {
  data: rolesData,
  execute: fetchRoles,
  isFetching: loading,
} = await useApi<any>(createUrl('/admin/roles', {
  query: {
    search,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const roles = computed(() => rolesData.value.data)
const permissions = computed(() => rolesData.value.permissions)
const total = computed(() => rolesData.value.meta.total)

const editItem = (item: any) => {
  editedIndex.value = roles.value.indexOf(item)
  item.permissions = item.permissions.map((r: any) => ({
    title: r.name,
    value: r.id,
  }))
  editedItem.value = Object.assign({}, item)
  dialog.value = true
}

const closeDelete = () => {
  dialogDelete.value = false
  nextTick(() => {
    editedItem.value = Object.assign({}, defaultItem.value)
    editedIndex.value = -1
  })
}

const deleteItemConfirm = async (role: Record<string, any>) => {
  await $api(`/admin/roles/${role.id}`, {
    method: 'DELETE',
    onResponseError(context) {
      console.log(context)
    },
  })

  roles.value.splice(editedIndex.value, 1)
  closeDelete()

  // refetch Roles
  await fetchRoles()
}

function close() {
  dialog.value = false
  nextTick(() => {
    editedItem.value = Object.assign({}, defaultItem.value)
    editedIndex.value = -1
  })
}

const save = () => {
  roleForm.value?.validate().then(async ({ valid: isValid }: boolean | any) => {
    if (!isValid)
      return

    if (editedIndex.value > -1) {
      const index = editedIndex.value

      const response = await $api(`/admin/roles/${editedItem.value.id}`, {
        method: 'POST',
        body: editedItem.value,
        onResponseError({ response: r }) {
          errors.value = r._data.errors
        },
      })

      Object.assign(roles.value[index], response.data)
    }
    else {
      await $api('/admin/roles/', {
        method: 'PUT',
        body: editedItem.value,
        onResponseError({ response: r }) {
          errors.value = r._data.errors
        },
      })
    }
    close()
    await fetchRoles()
  })
}

watch(dialog, val => {
  val || close()
})

watch(dialogDelete, val => {
  val || closeDelete()
})
</script>

<template>
  <VCard class="mx-auto">
    <VDataTableServer
      v-model:items-per-page="itemsPerPage"
      v-model:page="page"
      v-model:search="search"
      :items-per-page-options="itemsPerPageOptions"
      :headers="headers"
      :items="roles"
      :items-length="total"
      :loading
      class="pb-5"
      @update:options="updateOptions"
    >
      <template #top>
        <VToolbar
          color="transparent"
          flat
        >
          <VCardTitle>{{ $t('admin.roles.list.title') }}</VCardTitle>
          <VSpacer />
          <VDialog
            v-model="dialog"
            max-width="500px"
            persistent
          >
            <template #activator="{ props }">
              <VBtn
                variant="flat"
                color="success"
                prepend-icon="mdi-refresh"
                @click="fetchRoles"
              >
                {{ $t('admin.roles.list.refresh') }}
              </VBtn>
              <VBtn
                variant="flat"
                v-bind="props"
                class="mx-2"
              >
                <VIcon
                  start
                  icon="tabler-pencil-plus"
                />
                {{ $t('admin.roles.list.create') }}
              </VBtn>
            </template>
            <VCard>
              <VCardTitle>
                <span class="text-h5">{{ formTitle }}</span>
              </VCardTitle>
              <VCardText>
                <VContainer>
                  <VForm ref="roleForm">
                    <VRow>
                      <VCol cols="12">
                        <VTextField
                          v-model="editedItem.name"
                          :label="t('admin.roles.list.name')"
                          :rules="[requiredValidator]"
                          :error-messages="errors?.name"
                        />
                      </VCol>
                      <VCol cols="12">
                        <VSelect
                          v-model="editedItem.permissions"
                          :items="permissions"
                          :label="$t('admin.roles.list.permission-name')"
                          :rules="[requiredValidator]"
                          :error-messages="errors.permissions"
                          return-object
                          chips
                          multiple
                        />
                      </VCol>
                    </VRow>
                  </VForm>
                </VContainer>
              </VCardText>
              <VCardActions>
                <VSpacer />
                <VBtn
                  color="blue-darken-1"
                  variant="text"
                  @click="close"
                >
                  {{ $t('admin.roles.list.cancel') }}
                </VBtn>
                <VBtn
                  color="success"
                  variant="text"
                  @click="save"
                >
                  {{ $t('admin.roles.list.save') }}
                </VBtn>
              </VCardActions>
            </VCard>
          </VDialog>
        </VToolbar>
      </template>

      <template #item.name="{ item }">
        <strong>
          {{ item.name }}
        </strong>
      </template>

      <template #item.permissions="{ item }">
        <VChipGroup>
          <VChip
            v-for="(permission, key) in item.permissions"
            :key="key"
            class="me-1 mb-1"
            label
          >
            {{ permission.name }}
          </VChip>
        </VChipGroup>
      </template>

      <template #item.timestamps="{ item }">
        <div class="d-flex flex-column ms-3">
          <small class="d-block font-weight-medium text--primary text-truncate">{{ $t('admin.roles.list.created') }}: {{ formatDate(item.created_at) }}</small>
          <small class="d-block font-weight-medium text--primary text-truncate">{{ $t('admin.roles.list.updated') }}: {{ formatDate(item.updated_at) }}</small>
        </div>
      </template>

      <template #item.actions="{ item }">
        <VIcon
          start
          size="small"
          @click="editItem(item)"
        >
          mdi-pencil
        </VIcon>
        <VIcon
          size="small"
          @click="deleteItemConfirm(item)"
        >
          mdi-delete
        </VIcon>
      </template>

      <template #no-data>
        <VBtn
          color="primary"
          @click="fetchRoles"
        >
          {{ $t('admin.roles.list.update-data-server') }}
        </VBtn>
      </template>
    </VDataTableServer>
  </VCard>
</template>
