import type { VerticalNavItems } from '@layouts/types'

import first from './first'
import admin from './admin'
import payments from './payments'

import charts from './charts'
import dashboard from './dashboard'
import forms from './forms'
import uiElements from './ui-elements'

export default [
  // ...dashboard, ...appsAndPages, ...uiElements, ...forms, ...charts, ...others
  ...admin, ...first, ...payments
] as VerticalNavItems
