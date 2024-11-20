<script lang="ts" setup>
import { mergeProps } from 'vue'
import { getI18n } from '@/plugins/i18n'
import ForceStopComponent from '@/views/user/accounts/telegram/ForceStopComponent.vue'
import ClearSessionComponent from '@/views/user/accounts/telegram/ClearSessionComponent.vue'
import ShowTelegramAuthCodeComponent from '@/views/user/accounts/telegram/ShowTelegramAuthCodeComponent.vue'
import { useAccountsStore } from '@/stores/AccountsStore'
import { useMailingStore } from '@/stores/MailingStore'
import type { SortItem } from '@core/types'
import SettingsDrawerComponent from '@/views/user/mailing/settings/SettingsDrawerComponent.vue'
import StateSwitch from "@/views/user/mailing/MailingStateSwitch.vue";
import { MailingClient } from "@/stores/types/mailing";
import ActionUserDrawer from "@/views/admin/users/ActionUserDrawer.vue";
import { MailingProperties } from "@/views/user/mailing/types";
import MailingItems from "@/views/user/mailing/MailingItems.vue";
import type {VForm} from "vuetify/lib/components/VForm";

const { t } = getI18n().global

const mailingSettingsDrawer = ref(false)
const isActionMailingDrawerVisible = ref(false)
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
<!--      <template #item.step="{ item }">-->
<!--        {{ accountsStore.getStep(item) ? $t(accountsStore.getStep(item)?.message ?? 'stateOffline') : $t('stateOffline') }}-->
<!--      </template>-->

<!--      <template #item.action="{ item }">-->
<!--        <VListItem v-if="item.step?.value === 0">-->
<!--          <VListItemSubtitle>Если инициализация аккаунта занимает больше минуты</VListItemSubtitle>-->
<!--          <VListItemSubtitle>Выключите и снова включите желтый переключатель справа</VListItemSubtitle>-->
<!--        </VListItem>-->
<!--        <VListItem>-->
<!--          <ShowTelegramAuthCodeComponent-->
<!--            :account="item"-->
<!--            :key="`show-qr-code-component-${item.login}`"-->
<!--          />-->
<!--        </VListItem>-->
<!--      </template>-->

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

          <VIcon
            size="small"
            @click="deleteMailing(item)"
          >
            mdi-delete
          </VIcon>

<!--          <VMenu transition="fade-transition">-->
<!--            <template v-slot:activator="{ props: menu }">-->
<!--              <VTooltip location="top left">-->
<!--                <template v-slot:activator="{ props: tooltip }">-->
<!--                  <IconBtn-->
<!--                    icon="tabler-dots-vertical"-->
<!--                    v-bind="mergeProps(menu, tooltip)"-->
<!--                  />-->
<!--                </template>-->
<!--                <span>{{ $t('accounts.telegram.menu.tooltip') }}</span>-->
<!--              </VTooltip>-->
<!--            </template>-->
<!--            <VCard>-->
<!--              <VListItem :key="`${$dayjs()}-telegram-force-stop`">-->
<!--                <ForceStopComponent :account="item" />-->
<!--              </VListItem>-->
<!--              <VListItem :key="`${$dayjs()}-telegram-clear-session`">-->
<!--                <ClearSessionComponent :account="item" />-->
<!--              </VListItem>-->
<!--              <VListItem :key="`${$dayjs()}-telegram-delete-account`">-->
<!--                <DeleteAccountComponent :account="item"/>-->
<!--              </VListItem>-->
<!--            </VCard>-->
<!--          </VMenu>-->
        </div>
      </template>
      <template #item.combined="{ item }">
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
  </div>
</template>
