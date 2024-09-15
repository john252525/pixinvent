<script lang="ts" setup>
defineOptions({
  name: 'AppDatePicker',
  inheritAttrs: false,
})

const dateModel = defineModel({ required: true })

const dayjs = inject('dayjs')
const attrs = useAttrs()
const menu = ref(false)

const pickerData = computed({
  get() {
    return dateModel.value
  },
  set(value) {
    if (dayjs(value).isValid())
      return dateModel.value = dayjs?.utc(value).local().format()

    return dateModel.value = null
  },
})

const dateData = computed({
  get() {
    if (dayjs(dateModel.value).isValid())
      return dayjs?.utc(dateModel.value).local().format('LL')

    return null
  },
  set() {
    return dayjs?.utc(dateModel.value).local().format('LL')
  },
})

const elementId = computed(() => {
  const _elementIdToken = attrs.id || attrs.label

  return _elementIdToken ? `app-text-field-${_elementIdToken}-${Math.random().toString(36).slice(2, 7)}` : undefined
})

const openDatePicker = () => {
  menu.value = true
}

const closeDatePicker = () => {
  menu.value = false
}
</script>

<template>
  <div
    class="app-text-field flex-grow-1"
    :class="$attrs.class"
  >
    <VTextField
      v-model="dateData"
      :disabled="menu"
      v-bind="{
        ...$attrs,
        id: elementId,
      }"
      @click="openDatePicker"
    >
      <template #append-inner>
        <VMenu
          v-model="menu"
          :close-on-content-click="false"
          transition="scale-transition"
          offset-y
          min-width="290px"
        >
          <template #activator="{ props }">
            <IconBtn
              v-bind="props"
              :style="{ left: '50%', transform: 'translateX(-50%)' }"
            >
              <VIcon icon="mdi-calendar-month-outline" />
            </IconBtn>
          </template>
          <VDatePicker
            v-model="pickerData"
            hide-header
            show-adjacent-months
          >
            <template #actions>
              <VBtn
                color="primary"
                density="compact"
                @click="closeDatePicker"
              >
                Ok
              </VBtn>
            </template>
          </VDatePicker>
        </VMenu>
      </template>
      <template #clear>
        <VBtn
          color="transparent"
          density="compact"
          icon="mdi-close"
          @click="pickerData = null"
        />
      </template>
      <template
        v-for="(_, name) in $slots"
        #[name]="slotProps"
      >
        <slot
          :name="name"
          v-bind="slotProps || {}"
        />
      </template>
    </VTextField>
  </div>
</template>
