<script setup lang="ts">
const component =  defineModel('component')
</script>

<template>
  <VCol v-bind="component.cols">
    <VListItem
      v-if="component.type === 'title'"
      :title="component.title"
      :subtitle="component.subtitle"
      class="px-0"
    />

    <VTextField
      v-if="component.type === 'input'"
      :label="component.label"
      :rules="component.required ? [requiredValidator] : []"
      v-model="component.value"
    />

    <VTextarea
      v-if="component.type === 'textarea'"
      :label="component.label"
      :rows="component.rows"
      :rules="component.required ? [requiredValidator] : []"
    />

    <VCheckbox
      v-if="component.type === 'checkbox'"
      :label="component.label"
      :rules="component.required ? [requiredValidator] : []"
    />

    <VSelect
      v-if="component.type === 'select'"
      :label="component.label"
      :items="component.items"
      :rules="component.required ? [requiredValidator] : []"
    />

    <VRadioGroup
      v-if="component.type === 'radio'"
      :label="component.label"
      :rules="component.required ? [requiredValidator] : []"
    >
      <VRadio
        v-for="(item, index) in component.items"
        :key="index"
        :label="item.label"
        :value="item.value"
        :rules="component.required ? [requiredValidator] : []"
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
</template>

<style scoped lang="scss">

</style>
