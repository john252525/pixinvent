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

// Определяем тип для значений value
const apiStates = {
  null: 'stateOffline',
  0: 'Account just started to start',
  2.2: 'QR code received',
  2.3: 'Can not update QR',
  2.22: 'Код авторизации получен',
  5: 'Account started successfully & realtime init done',
  'telegram': {
    '0': 'Account just started to start',
    '2.1': 'This account needs 2FA solving',
    '2.2': 'QR code received',
    '2.25': 'This account needs challenge solving',
    '2.3': 'Can not update QR',
    '2.22': 'Код авторизации получен',
    '2.5': 'Another error during account launch',
    '5': 'Account started successfully & realtime init done',
  }
}

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

          <ForceStopComponent :account="item" />

          <StateSwitch
            source="whatsapp"
            :account="item"
            :key="`state-switch-${item.login}`"
            @check-state="accountsStore.getState(item)?.currentState"
          />

          <IconBtn @click="editWebhookUrls(item)">
            <VIcon icon="mdi-settings" />
          </IconBtn>

          <VMenu>
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
            <VList>
              <VListItem :key="`${$dayjs()}-1`">
                <ClearSessionComponent :account="item" />
              </VListItem>
              <VListItem :key="`${$dayjs()}-2`">
                <DeleteAccountComponent :account="item"/>
              </VListItem>
            </VList>
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
