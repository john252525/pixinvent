<script lang="ts" setup>
import type { Dayjs } from 'dayjs'

definePage({
  meta: {
    action: 'read',
    subject: 'admin',
  },
})

const { t } = useI18n()
const dialog = ref(false)
const dialogColor = ref(false)
const dialogDelete = ref(false)
const permissionForm = ref()
const searchQuery = ref('')
const page = ref(1)
const sortBy = ref('id')
const orderBy = ref('desc')

const headers = ref([
  {
    title: t('permissions.list.name'),
    align: 'start',
    sortable: false,
    key: 'name',
  },
  { title: t('permissions.list.guard'), key: 'guard_name', align: 'center' },
  { title: t('permissions.list.time'), key: 'timestamps', align: 'start', sortable: false },
  { title: t('permissions.list.actions'), key: 'actions', align: 'end', sortable: false },
])

const dayjs = inject<Dayjs | any>('dayjs')
const editedIndex = ref(-1)
const itemsPerPage = ref(10)

const errors = ref({
  name: undefined,
  guard_name: undefined,
})

const updateOptions = (options: any) => {
  page.value = options.page
  sortBy.value = options.sortBy[0]?.key
  orderBy.value = options.sortBy[0]?.order
}

const itemsPerPageOptions = ref([
  { value: 3, title: '3' },
  { value: 5, title: '5' },
  { value: 10, title: '10' },
  { value: 50, title: '50' },
])

const editedItem = ref({
  id: null,
  name: '',
  guard_name: 'web',
  meta: {
    color: 'default',
    description: '',
  },
})

const defaultItem = ref({
  id: null,
  name: '',
  guard_name: 'web',
  meta: {
    color: 'default',
    description: '',
  },
})

function formatDate(date: Dayjs) {
  return dayjs(date).fromNow()
}

const formTitle = computed(() => {
  return editedIndex.value === -1 ? 'permissions.list.create-permission' : 'permissions.list.edit-permission'
})

const {
  data: permissionsData,
  execute: fetchPermissions,
} = await useApi<any>(createUrl('/admin/permissions', {
  query: {
    search: searchQuery,
    itemsPerPage,
    page,
    sortBy,
    orderBy,
  },
}))

const permissions = computed(() => permissionsData.value.data)
const itemsLength = computed(() => permissionsData.value.meta.total)

function editItem(item: any | never) {
  editedIndex.value = permissions.value.indexOf(item)
  editedItem.value = Object.assign({}, item)
  dialog.value = true
}

async function deleteItem(permission: any | never) {
  await $api(`/admin/permissions/${permission.id}`, {
    method: 'DELETE',
    onResponseError(context) {
      console.log(context)
    },
  })

  // refetch Permissions
  await fetchPermissions()
}

function close() {
  dialog.value = false
  nextTick(() => {
    editedItem.value = Object.assign({}, defaultItem.value)
    editedIndex.value = -1
  })
}

function closeDelete() {
  dialogDelete.value = false
  nextTick(() => {
    editedItem.value = Object.assign({}, defaultItem.value)
    editedIndex.value = -1
  })
}

function save() {
  permissionForm.value?.validate().then(async ({ valid: isValid }: any) => {
    if (!isValid)
      return
    if (editedIndex.value > -1) {
      const index = editedIndex.value

      const response = await $api(`/admin/permissions/${editedItem.value.id}`, {
        method: 'POST',
        body: editedItem.value,
        onResponseError({ response: r }) {
          errors.value = r._data.errors
        },
      })

      Object.assign(permissions.value[index], response.data)
    }
    else {
      await $api('/admin/permissions/', {
        method: 'PUT',
        body: editedItem.value,
        onResponseError({ response: r }) {
          errors.value = r._data.errors
        },
      })
    }

    close()
    await fetchPermissions()
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
      :items-per-page-options="itemsPerPageOptions"
      :headers="headers"
      :items-length="itemsLength"
      :items="permissions"
      @update:options="updateOptions"
    >
      <template #top>
        <VToolbar
          color="transparent"
          flat
        >
          <VCardTitle>{{ $t('permissions.list.list-permissions') }}</VCardTitle>
          <VSpacer />
          <VDialog
            v-model="dialog"
            persistent
            max-width="500px"
          >
            <template #activator="{ props }">
              <VBtn
                variant="flat"
                color="success"
                prepend-icon="mdi-refresh"
                @click="fetchPermissions"
              >
                {{ $t('permissions.list.refresh') }}
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
                Создать
              </VBtn>
            </template>
            <VCard>
              <VCardTitle>
                <span class="text-h5">{{ formTitle }}</span>
              </VCardTitle>
              <VCardText>
                <VContainer>
                  <VForm ref="permissionForm">
                    <VRow>
                      <VCol cols="12">
                        <VTextField
                          v-model="editedItem.name"
                          :label="$t('permissions.list.permission-name')"
                          :rules="[requiredValidator]"
                          :error-messages="errors.name"
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
                  {{ $t('permissions.list.cancel') }}
                </VBtn>
                <VBtn
                  color="success"
                  variant="text"
                  @click="save"
                >
                  {{ $t('permissions.list.save') }}
                </VBtn>
              </VCardActions>
            </VCard>
          </VDialog>
          <VDialog
            v-model="dialogDelete"
            persistent
            min-width="350px"
            max-width="400px"
          >
            <VCard>
              <VCardTitle class="text-h5">
                {{ $t('permissions.list.delete') }}
              </VCardTitle>
              <VCardSubtitle>
                {{ $t('permissions.list.action-irreversible') }}
              </VCardSubtitle>
              <VCardActions>
                <VSpacer />
                <VBtn
                  color="blue-darken-1"
                  variant="text"
                  @click="closeDelete"
                >
                  {{ $t('permissions.list.cancel') }}
                </VBtn>
                <VBtn
                  color="error"
                  variant="text"
                  @click="deleteItem"
                >
                  {{ $t('permissions.list.delete') }}
                </VBtn>
                <VSpacer />
              </VCardActions>
            </VCard>
          </VDialog>
        </VToolbar>
      </template>
      <template #item.name="{ item }">
        <strong>{{ item.name }}</strong>
      </template>
      <template #item.timestamps="{ item }">
        <div class="d-flex flex-column ms-3">
          <small class="d-block font-weight-medium text--primary text-truncate">
            {{ $t('permissions.list.created') }}:
            {{ formatDate(item.created_at) }}
          </small>
          <small class="d-block font-weight-medium text--primary text-truncate">
            {{ $t('permissions.list.updated') }}:
            {{ formatDate(item.updated_at) }}
          </small>
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
          @click="deleteItem(item)"
        >
          mdi-delete
        </VIcon>
      </template>
      <template #no-data>
        <VBtn
          color="primary"
          @click="fetchPermissions"
        >
          {{ $t('permissions.list.refresh') }}
        </VBtn>
      </template>
    </VDataTableServer>
  </VCard>
</template>
