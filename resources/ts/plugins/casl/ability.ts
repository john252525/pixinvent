import { createMongoAbility } from '@casl/ability'

export const abilityActions = ['create','read', 'update', 'delete','manage'] as const
export type Actions = typeof abilityActions[number]

export const abilitySubjects = ['admin', 'user', 'accounts', 'settings', 'reserved', 'binder', 'all'] as const
export type Subjects = typeof abilitySubjects[number]

export interface Rule { action: Actions; subject: Subjects }

export const ability = createMongoAbility<[Actions, Subjects]>()
