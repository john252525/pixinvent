<script lang="ts" setup>
import type { Notification } from '@layouts/types'
import {router} from "@/plugins/1.router";

const userStore = useUserStore()

const removeNotification = (notificationId: number) => {
  userStore.notifications.forEach((item, index) => {
    if (notificationId === item.id)
      userStore.notifications.splice(index, 1)
  })
}

const markRead = (notificationId: number[]) => {
  userStore.notifications.forEach(item => {
    notificationId.forEach(id => {
      if (id === item.id)
        item.isSeen = true
    })
  })
}

const markUnRead = (notificationId: number[]) => {
  userStore.notifications.forEach(item => {
    notificationId.forEach(id => {
      if (id === item.id)
        item.isSeen = false
    })
  })
}

const removeAll = () => {
  userStore.notifications = []
}

const handleNotificationClick = (notification: Notification) => {
  if (!notification.isSeen)
    markRead([notification.id])
  if (notification.to)
    router.push(notification.to)
}
</script>

<template>
  <Notifications
    :notifications="userStore.notifications"
    @remove="removeNotification"
    @read="markRead"
    @unread="markUnRead"
    @remove-all="removeAll"
    @click:notification="handleNotificationClick"
  />
</template>
