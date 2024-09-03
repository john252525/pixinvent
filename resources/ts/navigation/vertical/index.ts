import first from './first'
import admin from './admin'
import charts from './charts'
import dashboard from './dashboard'
import forms from './forms'
import uiElements from './ui-elements'
import type { VerticalNavItems } from '@layouts/types'

export default [
  // ...dashboard, ...appsAndPages, ...uiElements, ...forms, ...charts, ...others
  ...admin, ...first,
] as VerticalNavItems
