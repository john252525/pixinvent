import { getI18n } from "@/plugins/i18n";
import { MailingClient } from "@/stores/types/mailing";
import {FetchContext, FetchResponse} from "ofetch";

const { t } = getI18n().global

export const useMailingStore = defineStore('mailing-store', () => {
  const mailingStates = [
    { state: 1, value: 1, label: t('Account started successfully & realtime init done'), color: 'success', currentState: true, disabled: false, loading: false, indeterminate: false },
    { state: 0, value: 0, label: t('stateOffline'), color: 'red', currentState: false, disabled: false, loading: false, indeterminate: false },
    { state: 'connecting', value: 100, label: t('Connecting...'), color: 'secondary', currentState: false, disabled: true, loading: 'warning', indeterminate: true },
    { state: 'disconnecting', value: 200, label: t('Disconnecting...'), color: 'secondary', currentState: false, disabled: true, loading: 'error', indeterminate: true },
  ]


  const mailings = ref<MailingClient>({
    items: [],
  });
  const loading = ref({
    mailings: false,
  })

  const weekDays = [t('days.mon'), t('days.tue'), t('days.wed'), t('days.thur'), t('days.fri'), t('days.sat'), t('days.sun')];

  async function getMailings() {
    let items = [];

    loading.value.mailings = true

    try {
      const response = await $api(`/user/mailing/get`)

      items = response.result.items

      await getMailingMessages(items)
    } catch (error) {
      console.log(error)
      await getMailings()
    } finally {
      loading.value.mailings = false
    }
  }

  async function getMailingMessages(items)
  {
    for (const [index, item] of items.entries()) {
      try {
        const response = await $api(`/user/mailing/get-messages/${item.id}`)

        items[index].messages = {
          count: response.result.count,
          items: response.result.items,
        }
      } catch (error) {
        console.log(error)
        await getMailingMessages()
      }
    }

    mailings.value["items"] = items
  }

  function getMailing(mailing: MailingClient) {
    const mailingIndex = getMailingIndex(mailing)
    return mailingIndex !== -1 ? mailings.value["items"][mailingIndex] : mailings.value["items"].find((client: MailingClient) => mailing.id === client.id)
  }

  function getMailingIndex(mailing: MailingClient) {
    return mailings.value['items'].findIndex(client => mailing.id === client.id)
  }

  async function getMessages(id) {
    loading.value.mailing = true

    await $api(`/user/mailing/get-messages/${id}`, {
      method: 'GET',
      onResponse: ({ response }) => {
        console.log(response)
      },
      onResponseError({ response }) {
        console.log(response)
      },
    })
    loading.value.mailing = false
  }

  async function removeMailing(id) {
    await $api(`/user/mailing/delete/${id}`, {
      method: 'GET',
      onResponse: ({ response }) => {
        console.log(response)
      },
      onResponseError({ response }) {
        console.log(response, 'ERRRRRROOORRRRR');
      },
    });
  }

  function getState(mailing: MailingClient) {
    return mailingStates.find(state => state.value === (mailing?.state ?? null))
  }


  async function setMailingState(mailing: MailingClient) {
    const id = mailing.id;
    const state = mailing.state === 1 ? 0 : 1

    return await $api(`user/mailing/edit-status/${id}/${state}`, {
      method: 'GET',
      onResponse({ response }) {
        if (response._data.ok) {
          mailing.state = state === 1 ? 0 : 1
          setMailing(mailing)
        }

        console.log(response)
      },
      onResponseError({ response }) {
        console.log(response)
        getMailings()
      },
    })
  }

  function setMailing(client: MailingClient) {
    const mailingIndex = getMailingIndex(client)
    if (mailingIndex !== -1) {
      mailings.value["items"][mailingIndex] = client;
    }
  }

  function setState(mailing: MailingClient, stepValue: number) {
    const currentMailing = getMailing(mailing)
    if(currentMailing)
      mailing.step = { value: stepValue };
  }

  const convertToWeekDays = (numbers) => {
    const daysArray = numbers.map(Number);
    return daysArray
      .filter((num) => num >= 1 && num <= 7)
      .map((num) => weekDays[num - 1])
      .join(', ');
  };

  return {
    // states
    mailingStates,
    loading,
    mailings,

    // functions
    getMailings,
    getMailing,
    getMessages,
    getMailingMessages,
    removeMailing,
    convertToWeekDays,
    getState,
    setMailingState,
    setState
  }
});
