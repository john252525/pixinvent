<script setup lang="ts">
import { PerfectScrollbar } from 'vue3-perfect-scrollbar'
import type { VForm } from 'vuetify/components/VForm'
import type { UserProperties } from '@/views/admin/users/types'
import {MailingProperties} from "@/views/user/mailing/types";
import {useMailingStore} from "@/stores/MailingStore";

interface Emit {
  (e: 'update:isDrawerOpen', value: boolean): void
}

interface Props {
  isDrawerOpen: boolean,
  hours: {
    text: string;
    value: number;
  },
  mailingData: MailingProperties
}

const props = defineProps<Props>()
const emit = defineEmits<Emit>()

const mailing = ref<MailingProperties>({
  id: undefined,
  text: '',
  to: '',
})

const mailingStore = useMailingStore();

// 👉 drawer close
const closeNavigationDrawer = () => {
  emit('update:isDrawerOpen', false)
  document.body.style = '';
}

const handleDrawerModelValueUpdate = (val: boolean) => {
  resetForm()
}

watch(() => props.mailingData, (data) => {
  mailing.value = data.messages
})

const isDrawerOpens = ref(false)

watch(isDrawerOpens, (isOpen: boolean) => {
  console.log(isOpen)
})
</script>

<template>
  <Teleport to="body">
    <VNavigationDrawer
      temporary
      :width="400"
      location="end"
      persistent
      class="scrollable-content"
      :model-value="props.isDrawerOpen"
      @update:model-value="handleDrawerModelValueUpdate"
    >
      <!-- 👉 Title -->
      <AppDrawerHeaderSection
        :title="$t('mailings.view.title')"
        icon="mdi-account-edit"
        @cancel="closeNavigationDrawer"
      />

      <VDivider />

      <PerfectScrollbar :options="{ wheelPropagation: false }">
        <VList>
          <VListItem
            v-for="item in mailing.items"
          >
          <VListItemContent>
            <VCard class="d-flex align-center">
              <VListItemTitle class="pa-2 border-e">{{ item.id }}</VListItemTitle>
              <div class="pl-2">
                <VListItemTitle>{{ item.text }}</VListItemTitle>
                <VListItemSubtitle>{{ $t('mailings.view.to') }}: {{ item.to }}</VListItemSubtitle>
                <VListItemSubtitle>{{ $t('Delivered') }}: {{ item.state ? $t('Yes') : $t('No') }}</VListItemSubtitle>
              </div>
            </VCard>
          </VListItemContent>
          </VListItem>
        </VList>
      </PerfectScrollbar>
    </VNavigationDrawer>
  </Teleport>
</template>

<style lang="scss">
//
</style>
