<script lang="ts" setup>
import { mergeProps } from 'vue'
import { getI18n } from '@/plugins/i18n'
import type { AccountClient } from '@/stores/types/accounts'
import StateSwitch from '@/views/user/accounts/whatsapp/WhatsappStateSwitch.vue'
import ForceStopComponent from '@/views/user/accounts/whatsapp/ForceStopComponent.vue'
import ClearSessionComponent from '@/views/user/accounts/whatsapp/ClearSessionComponent.vue'
import ShowWhatsappAuthCodeComponent from '@/views/user/accounts/whatsapp/ShowWhatsappAuthCodeComponent.vue'
import { useAccountsStore } from '@/stores/AccountsStore'
import type { SortItem } from '@core/types'
import DeleteAccountComponent from '@/views/user/accounts/whatsapp/DeleteAccountComponent.vue'
import SettingsDrawerComponent from '@/views/user/accounts/whatsapp/settings/SettingsDrawerComponent.vue'

const { t } = getI18n().global
const accountSettingsDrawer = ref(false)
const account = ref<AccountClient | null>()

const accountsStore = useAccountsStore()

const whatsappMenu = ref(false)

const sortBy = ref<SortItem[]>([{ key: 'login', order: 'asc' }])
const headers = ref([
  { title: t('accLogin'), key: 'login', align: 'start' },
  { title: t('Step'), key: 'step', align: 'start' },
  { title: t('Action'), key:'action', align:'start', sortable: false, width: '100%' },
  { title: t('Actions'), key: 'actions', align: 'end', sortable: false },
])

const updateAccountLoader = ref(false)
const editWebhookUrls = (client: AccountClient) => {
  account.value = client
  accountSettingsDrawer.value = true
}

const updateIsDrawerOpen = (isDrawerOpen: boolean) => {
  accountSettingsDrawer.value = isDrawerOpen
}
</script>

<template>
  <div>
    <VDataTable
      :sort-by="sortBy"
      :items="accountsStore.accounts.whatsapp"
      :loading="accountsStore.loading.accounts"
      :headers
      class="text-no-wrap users-table"
    >
      <template #item.step="{ item }">
        {{ accountsStore.getStep(item) ? $t(accountsStore.getStep(item).message) : $t('stateOffline') }}
      </template>

      <template #item.action="{ item }">
        <VListItem v-if="item.step?.value === 0">
          <VListItemSubtitle>Если инициализация аккаунта занимает больше минуты</VListItemSubtitle>
          <VListItemSubtitle>Выключите и снова включите желтый переключатель справа</VListItemSubtitle>
        </VListItem>
        <VListItem>
          <ShowWhatsappAuthCodeComponent
            :account="item"
            :key="`show-qr-code-component-${item.login}`"
          />
        </VListItem>
      </template>

      <template #item.actions="{ item }">
        <div class="d-flex float-end gap-2">

          <StateSwitch
            source="whatsapp"
            :account="item"
            :key="`state-switch-${item.login}`"
            @check-state="accountsStore.getState(item)?.currentState"
          />

          <IconBtn @click="editWebhookUrls(item)">
            <VIcon icon="mdi-settings" />
          </IconBtn>

          <VMenu transition="fade-transition">
            <template v-slot:activator="{ props: menu }">
              <VTooltip location="top left">
                <template v-slot:activator="{ props: tooltip }">
                  <IconBtn
                    icon="tabler-dots-vertical"
                    v-bind="mergeProps(menu, tooltip)"
                  />
                </template>
                <span>{{ $t('accounts.whatsapp.menu.tooltip') }}</span>
              </VTooltip>
            </template>
            <VCard>
              <VListItem :key="`${$dayjs()}-whatsapp-1`">
                <ForceStopComponent :account="item" />
              </VListItem>
              <VListItem :key="`${$dayjs()}-whatsapp-2`">
                <ClearSessionComponent :account="item" />
              </VListItem>
              <VListItem :key="`${$dayjs()}-whatsapp-3`">
                <DeleteAccountComponent :account="item" />
              </VListItem>
            </VCard>
          </VMenu>
        </div>
      </template>
    </VDataTable>
    <SettingsDrawerComponent
      :account :key="`settings-drawer-${account?.login}`"
      :isDrawerOpen="accountSettingsDrawer"
      @is-drawer-open="updateIsDrawerOpen" />
  </div>
</template>
