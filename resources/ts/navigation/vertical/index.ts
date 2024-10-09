import type { VerticalNavItems } from '@layouts/types'

import user from './user'
import admin from './admin'
import payments from './payments'

export default [
  ...admin, ...user, ...payments,
] as VerticalNavItems
