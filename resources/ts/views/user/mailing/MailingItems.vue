<script lang="ts" setup>
import { getI18n } from '@/plugins/i18n'
import { useAccountsStore } from '@/stores/AccountsStore'
import { useMailingStore } from '@/stores/MailingStore'
import type { SortItem } from '@core/types'
import SettingsDrawerComponent from '@/views/user/mailing/settings/SettingsDrawerComponent.vue'
import StateSwitch from "@/views/user/mailing/MailingStateSwitch.vue";
import { MailingProperties } from "@/views/user/mailing/types";
import ViewDrawerComponent from "@/views/user/mailing/settings/ViewDrawerComponent.vue";

const { t } = getI18n().global

const mailingSettingsDrawer = ref(false)
const isActionMailingDrawerVisible = ref(false)
const isActionMailingMessagesDrawerVisible = ref(false)

const mailing = ref<MailingProperties>({
  days: '',
  time_from: '',
  time_to: '',
  delay_from: '',
  delay_to: '',
  uniq: '',
  exist: '',
  random: '',
  cascade: '',
} as MailingProperties);

const accountsStore = useAccountsStore()
const mailingStore = useMailingStore();

const sortBy = ref<SortItem[]>([{ key: 'id', order: 'asc' }])
const headers = [
  { title: t('Title'), key: 'name', align: 'start' },
  // { title: '', key:'action', align:'start', sortable: false, width: '100%' },
  { title: t('Details'), key: 'combined', align: 'start' },
  // { title: '', key:'action', align:'start', sortable: false, width: '100%' },
  { title: t('Actions'), key: 'actions', align: 'end', sortable: false },
] as any[]

const actionMailing = (editedMailing: any = {}) => {
  mailing.value = editedMailing

  isActionMailingDrawerVisible.value = true
  document.body.style = 'position: relative;'
}

const actionMailingMessages = (editedMailing: any = {}) => {
  mailing.value = editedMailing

  isActionMailingMessagesDrawerVisible.value = true
  document.body.style = 'position: relative;'
}

const search = ref()

const updateIsDrawerOpen = (isDrawerOpen: boolean) => {
  mailingSettingsDrawer.value = isDrawerOpen
}

const deleteMailing = async (deletedMailing: any) => {
  await $api(`/user/mailing/delete/${deletedMailing.id}`, {
    method: 'DELETE',
  })

  mailingStore.getMailings()
}
</script>

<template>
  <div>
    <VDataTable
      :sort-by="sortBy"
      :items="mailingStore.mailings.items"
      :loading="mailingStore.loading.mailings"
      :headers
      class="text-no-wrap users-table"
    >
      <template #item.actions="{ item }">

        <div class="d-flex float-end gap-2 align-center">

          <StateSwitch
            :mailing="item"
            :key="`state-switch-${item.name}`"
            @check-state="mailingStore.getState(item)?.currentState"
          />

          <IconBtn @click="actionMailing(item)">
            <VIcon icon="mdi-settings" />
          </IconBtn>

          <IconBtn v-if="item.messages.count !== 0" @click="actionMailingMessages(item)">
            <VIcon icon="mdi-eye" />
          </IconBtn>

          <VIcon
            size="small"
            @click="deleteMailing(item)"
          >
            mdi-delete
          </VIcon>
        </div>
      </template>

      <template #item.combined="{ item }">
        <p class="multiline-text">{{ $t('Messages') }}: {{ item.messages.count }}</p>
        <p class="multiline-text">{{ $t('Weekdays') }}: {{ mailingStore.convertToWeekDays(item.options.days) }}</p>
        <p class="multiline-text">{{ $t('Time') }}: {{ `${item.options.hours.min} - ${item.options.hours.max}` }}</p>
        <p class="multiline-text">{{ $t('Delay') }}: {{ `${item.options.delay.min} - ${item.options.delay.max}` }}</p>
        <p class="multiline-text">{{ $t('Date Create') }}: {{ `${item.dt_create}` }}</p>
      </template>
    </VDataTable>
    <SettingsDrawerComponent
      v-model:isDrawerOpen="isActionMailingDrawerVisible"
      v-model:mailing-data="mailing"
    />
    <ViewDrawerComponent
      v-model:isDrawerOpen="isActionMailingMessagesDrawerVisible"
      v-model:mailing-data="mailing"
    />
  </div>
</template>
