<script setup lang="ts">
import { useUserStore } from '@/stores/UserStore'
import { toMoney } from '@/utils/helpers'

const userStore = useUserStore()
</script>

<template>
  <VBtn
    variant="outlined"
    size="small"
    class="userBalance"
    @click="() => userStore.fetchUserData()"
  >{{ toMoney(userStore.userData?.balance || 0) }}</VBtn>
</template>
