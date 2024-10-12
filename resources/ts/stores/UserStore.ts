import { useAccountsStore } from './AccountsStore'
import { router } from '@/plugins/1.router'

export const useUserStore = defineStore('user-store', () => {
  const userData = ref({
    id: undefined,
    role: 'guest',
    balance: 0,
  })

  const toast = ref()
  const ability = useAbility()
  const accessToken = useCookie('accessToken')

  const userSettings = ref({})
  const userAbilityRules = ref([])

  const accountsStore = useAccountsStore()

  const setAbilities = (abilities: any) => {
    ability.update(abilities)
  }

  function fetchUserData(updateAbilities = false) {
    $api('/user', {
      onResponseError({ response }) {
        if(response.status === 401) {
          clearAllData()
        }
      },
      onResponse({ response }) {
        userData.value = response._data.userData
        userAbilityRules.value = response._data.userAbilityRules
        if(updateAbilities)
          setAbilities(userAbilityRules.value)
      }
    })
   }

  const clearAllData = () => {
    accountsStore.clearData()
    userData.value = ({
      id: undefined,
      role: 'guest',
      balance: 0,
    })
    userAbilityRules.value = []
    userSettings.value = {}
    useCookie('userData').value = null
    useCookie('userAbilityRules').value = null
    useCookie('accessToken').value = null

    setAbilities(userAbilityRules.value)

    nextTick().then(() => window.location.reload()) // router.push('/login'))
  }
  const logout = () => {
    return $api('/user/auth/logout', {
      method: 'POST',
      async onResponse({ response }) {
        toast.value.success('Вы успешно вышли из системы')
        clearAllData()
      },
      onResponseError({ response }) {
        toast.value.error('Произошла ошибка')
      }
    })
  }

  onMounted( () => {
    /*if(accessToken.value) {
      fetchUserData().then(() => {
        setAbilities(userAbilityRules.value)
      })
    }*/
  })

  return {
    // 👉 state
    userData,
    userAbilityRules,
    ability,
    toast,

    // 👉 getters

    // 👉 actions
    setAbilities,
    fetchUserData,
    logout,
    clearAllData,
  }
})
