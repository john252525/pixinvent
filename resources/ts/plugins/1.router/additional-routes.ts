import type { RouteRecordRaw } from 'vue-router/auto'

// const emailRouteComponent = () => import('@/pages/apps/email/index.vue')

// 👉 Redirects
export const redirects: RouteRecordRaw[] = [
  // ℹ️ We are redirecting to different pages based on role.
  // NOTE: Role is just for UI purposes. ACL is based on abilities.
  {
    path: '/',
    name: 'index',
    redirect: to => {
      // TODO: Get type from backend
      const userData = useCookie<Record<string, unknown> | null | undefined>('userData')
      const userRole = userData.value?.role

      if (userRole === 'sadmin')
        return { name: 'admin-users-list' }

      if (userRole === 'admin')
        return { name: 'access-control' }

      if (userRole === 'user')
        return { name: 'accounts' }

      return { name: 'login', query: to.query }
    },
  },
]

export const routes: RouteRecordRaw[] = []
