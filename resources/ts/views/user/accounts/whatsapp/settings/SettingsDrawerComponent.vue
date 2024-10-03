<script setup lang="ts">
import type { AccountClient } from '@/stores/types/accounts'
import { useAccountsStore } from '@/stores/AccountsStore'
import { getI18n } from '@/plugins/i18n'
import type { MaskaDetail, MaskInputOptions } from 'maska'

const props = defineProps(['isDrawerOpen'])
const account = defineModel<AccountClient>('account')
const emits = defineEmits(['isDrawerOpen'])

const { t } = getI18n().global

const updateAccountLoader = ref(false)
const showPhoneDialog = ref(false)
const showQrDialog = ref(false)
const accountsStore = useAccountsStore()
const phone = ref<number|string|undefined>(account.value?.additional.config.authPhone)
const phonePlaceholder = ref<number|string|undefined>(account.value?.additional.config.authPhone)
const stateText = ref()
const authMethod = ref<null|string>(null)

const onMaskaError = ref(true)

const maskaOptions = reactive<MaskInputOptions>({
  mask: (value: string) => value.startsWith('8') ? '8 (###) ###-##-##' : '+# (###) ###-##-##',
  eager: true,
  onMaska: (detail: MaskaDetail) => {
    onMaskaError.value = detail.completed
    phone.value = detail.unmasked
  },
})

const onMaska = (event: CustomEvent<MaskaDetail>) => {
  if (event.detail.completed) {
    phone.value = event.detail.unmasked.startsWith('7') ? event.detail.unmasked : `7${event.detail.unmasked}`
  } else {
    phone.value = undefined
  }
}

const saveWebhookUrls = async () => {
  updateAccountLoader.value = true
  if (account.value)
    await accountsStore.updateAccount(account.value, 'update-webhook-urls')

  updateAccountLoader.value = false
  emits('isDrawerOpen', false)
}

const cancelChangeAuth = (value: string) => {
  phone.value = undefined
  authMethod.value = null
  showPhoneDialog.value = false
  showQrDialog.value = false
  if (account.value)
    account.value.additional.config.services.authMethod = value === 'code' ? 'qr' : 'code'
}

const updateAuthMethod = async (value: string | null) => {
  if (!authMethod.value) {
    showPhoneDialog.value = value === 'code';
    showQrDialog.value = value === 'qr';
    authMethod.value = value;
    return;
  }

  showPhoneDialog.value = false;
  showQrDialog.value = false;
  updateAccountLoader.value = true;

  if (account.value) {
    await switchAccountState(account.value, false);
    await accountsStore.switchAuth(account.value, phone.value);
    await switchAccountState(account.value, true);
  }

  resetState();
}

const switchAccountState = async (accountValue: any, state: boolean) => {
  stateText.value = t(state ? 'accounts.states.messages.switching.on' : 'accounts.states.messages.switching.off');
  await accountsStore.switchState(accountValue, state, state);
}

const resetState = () => {
  updateAccountLoader.value = false;
  stateText.value = '';
  authMethod.value = null;
}
</script>

<template>
  <Teleport to="body">
    <VNavigationDrawer
      :model-value="props.isDrawerOpen"
      temporary
      width="350"
      location="right"
      absolute
      class="account-settings-drawer"
    >
      <VCardItem class="px-3">
        <VCardTitle>{{ $t('accounts.settings.title') }}</VCardTitle>
        <template #append>
          <IconBtn @click="$emit('isDrawerOpen', false)">
            <VIcon icon="mdi-close" />
          </IconBtn>
        </template>
      </VCardItem>
      <div v-if="account">
        <VCardTitle>{{ $t('accounts.settings.auth_method.title') }}</VCardTitle>
        <VListItem>
          <VRadioGroup
            v-model="account.additional.config.services.authMethod"
            :hint="stateText"
            persistent-hint
            @update:model-value="updateAuthMethod"
          >
            <VRadio :label="$t('accounts.settings.auth_method.label.qr')" value="qr"></VRadio>
            <VRadio :label="$t('accounts.settings.auth_method.label.code')" value="code"></VRadio>
          </VRadioGroup>
        </VListItem>

        <VList>
          <VCardTitle>{{ $t('accounts.settings.add_webhook_url.title') }}</VCardTitle>
          <VListItem v-for="(webhookUrl, index) in account.webhookUrls">
            <VTextarea
              v-model="account.webhookUrls[index]"
              :label="`${$t('accounts.settings.add_webhook_url.label')} ${index + 1}`"
              rows="6"
              class="py-3"
              counter
              clearable
            >
              <template #clear>
                <IconBtn class="position-absolute right-0 top-0" @click.stop="account.webhookUrls.splice(index, 1)">
                  <VIcon icon="mdi-close" />
                </IconBtn>
              </template>
            </VTextarea>
          </VListItem>
          <VCardActions>
            <VBtn
              block
              color="success"
              variant="outlined"
              @click="account.webhookUrls.push('')"
            >
              {{ $t('accounts.settings.add_webhook_url.button.add') }}
            </VBtn>
          </VCardActions>

          <VSpacer />

          <VDivider class="mb-3" />

          <VCardActions class="d-flex justify-space-between">
            <VBtn color="secondary" @click="$emit('isDrawerOpen', false)">Отмена</VBtn>
            <VBtn
              variant="flat"
              color="primary"
              :disabled="updateAccountLoader"
              :loading="updateAccountLoader"
              @click="saveWebhookUrls"
            >
              {{ $t('accounts.settings.button.save') }}
            </VBtn>
          </VCardActions>
        </VList>
      </div>
    </VNavigationDrawer>
  </Teleport>
  <VDialog
    v-model="showPhoneDialog"
    max-width="500"
  >
    <VCard
      :title="$t('accounts.settings.auth_method.phone.title')"
      min-height="300"
      flat
    >
      <VCardItem>
        <VTextField
          v-model="phonePlaceholder"
          class="my-3"
          v-maska="maskaOptions"
          :label="$t('accounts.settings.auth_method.phone.label')"
          inputmode="numeric"
          @maska="onMaska"
        />
      </VCardItem>

      <VSpacer />

      <VDivider />

      <VCardActions class="mt-3">
        <VBtn color="secondary" @click="cancelChangeAuth(authMethod ?? 'qr')">
          {{ $t('accounts.settings.auth_method.phone.button.cancel') }}
        </VBtn>
        <VBtn
          :disabled="!phone"
          variant="flat"
          color="primary"
          @click="updateAuthMethod(authMethod ?? 'code')"
        >
          {{ $t('accounts.settings.auth_method.phone.button.ok') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
  <VDialog
    v-model="showQrDialog"
    max-width="500"
  >
    <VCard
      :title="$t('accounts.settings.auth_method.qr.title')"
      min-height="300"
      flat
    >
      <VCardItem>{{ $t('accounts.settings.auth_method.qr.label') }}</VCardItem>

      <VSpacer />

      <VDivider />

      <VCardActions class="mt-3">
        <VBtn color="secondary" @click="cancelChangeAuth(authMethod ?? 'code')">
          {{ $t('accounts.settings.auth_method.qr.button.cancel') }}
        </VBtn>
        <VBtn
          variant="flat"
          color="primary"
          @click="updateAuthMethod(authMethod ?? 'qr')"
        >
          {{ $t('accounts.settings.auth_method.qr.button.ok') }}
        </VBtn>
      </VCardActions>
    </VCard>
  </VDialog>
</template>

<style lang="scss">
.account-settings-drawer {
  .v-navigation-drawer__content {
    overflow: auto;
  }
}
</style>
