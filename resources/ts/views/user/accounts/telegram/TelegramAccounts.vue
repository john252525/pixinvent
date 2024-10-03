<script lang="ts" setup>
import { getI18n } from '@/plugins/i18n'
import StateSwitch from '@/views/user/accounts/StateSwitch.vue'
import ToggleQrAuth from '@/views/user/accounts/whatsapp/ToggleQrAuth.vue'
import ForceStopComponent from '@/views/user/accounts/whatsapp/ForceStopComponent.vue'
import ClearSessionComponent from '@/views/user/accounts/whatsapp/ClearSessionComponent.vue'
import { useClipboard } from '@vueuse/core'
import ShowQrCodeComponent from '@/views/user/accounts/whatsapp/ShowAuthCodeComponent.vue'
import { useUserStore } from '@/stores/UserStore'
import { useAccountsStore } from '@/stores/AccountsStore'
import type { SortItem } from '@core/types'

const { t } = getI18n().global
const accountSettingsDrawer = ref(false)

const accountsStore = useAccountsStore()
const userStore = useUserStore()
const toast = userStore.toast
const { copy } = useClipboard()

const copyText = (text: string) => {
  copy(text)
  toast.success(t('Text copied to clipboard'))
}

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
</script>

<template>
  <div>
    <VDataTable
      :sort-by="sortBy"
      :items="accountsStore.accounts.telegram"
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
        <VListItem v-if="item.step?.value === 2.22">
          <VListItemSubtitle v-if="item.phone_code">
            Код авторизации WhatsApp:
            <VBtn
              variant="text"
              color="info"
              size="small"
              @click="copyText(item.phone_code)"
              >
               {{ item.phone_code }}
            </VBtn>
          </VListItemSubtitle>
          <VListItemSubtitle v-else>
            Для показа кода нажмите кнопку справа 👉
          </VListItemSubtitle>
          <template #append>
            <IconBtn v-if="!item.phone_code" @click.stop="accountsStore.getAuthCode(item)">
              <VTooltip :text="t('Click on this button to get WhatsApp authorization code')">
                <template v-slot:activator="{ props }">
                  <VIcon v-bind="props" icon="tabler-http-get" />
                </template>
              </VTooltip>
            </IconBtn>
            <div v-else>
              <IconBtn @click.stop="accountsStore.getAuthCode(item)">
                <VTooltip :text="t('If the code is outdated, click this button to update it')">
                  <template v-slot:activator="{ props }">
                    <VIcon v-bind="props" icon="mdi-refresh" />
                  </template>
                </VTooltip>
              </IconBtn>
              <IconBtn square @click.stop="accountsStore.getInfo(item)">
                <VTooltip :text="t('After entering the code in the WhatsApp application, click this button to update the status')">
                  <template v-slot:activator="{ props }">
                    <VIcon v-bind="props" icon="mdi-check" />
                  </template>
                </VTooltip>
              </IconBtn>
            </div>
          </template>
        </VListItem>
        <VListItem v-if="item.step?.value === 2.2">
          <span v-if="item.qr_code">
            Для показа QR кода нажмите кнопку справа 👉
          </span>
          <span v-else>
            Для показа QR кода нажмите кнопку справа 👉
          </span>
          <template #append>
            <ShowQrCodeComponent
              :account="item"
              :key="`show-qr-code-component-${item.login}`"
            />
          </template>
        </VListItem>
      </template>

      <template #item.actions="{ item }">
        <div class="d-flex float-end">

          <ForceStopComponent :account="item" />

          <IconBtn>
            <StateSwitch
              source="telegram"
              :model-value="item"
              :key="`state-switch-${item.login}`"
              @check-state="accountsStore.getState(item)?.currentState"
            />
          </IconBtn>

          <ToggleQrAuth :account="item" :key="`toggle-qr-auth-${item.login}`" />

          <IconBtn @click="accountSettingsDrawer = !accountSettingsDrawer">
            <VIcon icon="mdi-pencil" />
          </IconBtn>

          <ClearSessionComponent :account="item" />

          <IconBtn>
            <VIcon icon="mdi-delete" />
          </IconBtn>
        </div>
      </template>
    </VDataTable>
    <Teleport to="body">
      <VNavigationDrawer
        v-model="accountSettingsDrawer"
        temporary
        location="right"
      >
        <VListItem class="pe-0">
          <VListItemTitle>Настройки аккаунта</VListItemTitle>
          <template #append>
            <IconBtn @click.stop="accountSettingsDrawer = false">
              <VIcon icon="mdi-close" />
            </IconBtn>
          </template>
        </VListItem>
      </VNavigationDrawer>
    </Teleport>
  </div>
</template>
