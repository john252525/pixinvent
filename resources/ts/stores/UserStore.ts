export const useUserStore = defineStore('user-store', () => {
  const userData = ref({
    role: 'guest',
    balance: 0,
  })

  const toast = ref()
  const ability = useAbility()
  const accessToken = useCookie('accessToken').value

  const userSettings = ref({})
  const userAbilityRules = ref([])

  const setAbilities = (abilities: any) => {
    ability.update(abilities)
  }

  async function fetchUserData(updateAbilities = false) {
    await $api('/user', {
      onResponseError({ error }){
        console.info(error?.message)
      },
      onResponse({ response }) {
        userData.value = response._data.userData
        userAbilityRules.value = response._data.userAbilityRules
        if (updateAbilities)
          setAbilities(userAbilityRules.value)
      }
    })
  }

  const logout = async () => {
    useCookie('accessToken').value = null

    localStorage.removeItem('accessToken')
    userAbilityRules.value = []

    setAbilities(userAbilityRules.value)
    userData.value = Object({})

    return true
  }

  onBeforeMount(async () => {
    if (userData.value.id === undefined && accessToken !== undefined) {

      await fetchUserData()
        .then(() => {
          setAbilities(userAbilityRules.value)
        })
    }
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
  }
})
