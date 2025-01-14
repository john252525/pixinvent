<script setup lang="ts">
import { useHead } from '@unhead/vue'
import AutoPage from '@/views/autopage/AutoPage.vue'

definePage({
  meta: {
    action: 'read',
    subject: 'settings',
  },
})

const url = 'https://indiparser.apitter.com/?user_id=6'
const pageData = await $api(url)

// const components = computed(() => pageData.components)
const components = ref(pageData.components)
const submit = ref(pageData.submit)
const title = ref(pageData.title)
const label = ref(pageData.label)
const refVForm = ref()

useHead({
  title: () => title.value,
})

const submitForm = () => {
  refVForm.value.validate().then(async ({ valid: isValid }: any) => {
    if (isValid) {
      let formData = new FormData()
      components.value.forEach((item: any) => {
        if (item.name) {
          formData[item.name] = item.value || null // Если value нет, то присваиваем null
        }
      })

      // TODO: Change userId from store
      formData.user_id = useCookie('userData').value.id


      const resp = await $api(url, {
        method: 'POST',
        body: { ...formData },
      })

      components.value = resp.components
      submit.value = resp.submit
      title.value = resp.title
      label.value = resp.label
    }
  })
}

/*
const userID = useCookie('userData').value.id

const pageData = await $api(`/user/settings/get-settings`, {
  method: 'POST',
  body: { user_id: userID }
})

const components = ref(pageData.components)
const submit = ref(pageData.submit)
const title = ref(pageData.title)
const label = ref(pageData.label)
const refVForm = ref()

useHead({
  title: () => title.value,
})

const submitForm = () => {

  refVForm.value.validate().then(async ({ valid: isValid }: any) => {
    if (isValid) {
      let formData = new FormData()
      components.value.forEach((item: any) => {
        if (item.name) {
          formData[item.name] = item.value || null
        }
      })

      // TODO: Change userId from store
      formData.user_id = useCookie('userData').value.id


      const resp = await $api(`/user/settings/save-settings`, {
        method: 'POST',
        body: { ...formData },
      })

     

      components.value = resp.components
   
      submit.value = resp.submit
      title.value = resp.title
      label.value = resp.label
    }
  })
}
*/
</script>

<template>
  <VForm ref="refVForm" @submit.prevent="submitForm">
    <VCard>
      <VCardItem>
        <VCardTitle>{{ label }}</VCardTitle>
        <VCol
          v-for="(component, key) in components"
          v-bind="component.cols"
          :key
        >
          <VListItem
            v-if="component.type === 'title'"
            :title="component.title"
            :subtitle="component.subtitle"
            class="px-0"
          />

          <VTextField
            v-if="component.type === 'input'"
            v-model="component.value"
            :label="component.label"
            :rules="component.required ? [requiredValidator] : []"
            :error-messages="component.error"
            @update:model-value="component.error = undefined"
          />

          <VTextarea
            v-if="component.type === 'textarea'"
            v-model="component.value"
            :label="component.label"
            :rows="component.rows"
            :rules="component.required ? [requiredValidator] : []"
            :error-messages="component.error"
            @update:model-value="component.error = undefined"
          />

          <VCheckbox
            v-if="component.type === 'checkbox'"
            v-model="component.value"
            :label="component.label"
            :rules="component.required ? [requiredValidator] : []"
            :error-messages="component.error"
            @update:model-value="component.error = undefined"
          />

          <VSelect
            v-if="component.type === 'select'"
            v-model="component.value"
            :label="component.label"
            :items="component.items"
            :rules="component.required ? [requiredValidator] : []"
            :error-messages="component.error"
            @update:model-value="component.error = undefined"
          />

          <VRadioGroup
            v-if="component.type === 'radio'"
            v-model="component.value"
            :label="component.label"
            :rules="component.required ? [requiredValidator] : []"
            :error-messages="component.error"
            @update:model-value="component.error = undefined"
          >
            <VRadio
              v-for="(item, index) in component.items"
              :key="index"
              :label="item.label"
              :value="item.value"
              :rules="component.required ? [requiredValidator] : []"
              :error-messages="component.error"
              @update:model-value="component.error = undefined"
            />
          </VRadioGroup>

          <VDivider v-if="component.type === 'divider'" />

          <VCardTitle v-if="component.type === 'post'">
            {{ component.title }}
          </VCardTitle>
          <VCardText
            v-if="component.type === 'post'"
            class="px-0"
          >
            {{ component.text }}
          </VCardText>

          <VDataTable
            v-if="component.type === 'table'"
            :headers="component.headers"
            :items="component.items"
            :items-per-page="component.itemsPerPage"
            :items-per-page-options="component.itemsPerPageOptions"
          >
            <template #top>
              <VCardTitle>
                {{ component.title }}
              </VCardTitle>
            </template>
          </VDataTable>
        </VCol>
      </VCardItem>
      <VCardActions class="float-end">
        <VBtn
          :variant="submit.variant"
          type="submit"
          :disabled="submit.disabled"
        >
          {{ submit.label }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VForm>
</template>

<style scoped lang="scss">
//
</style>
