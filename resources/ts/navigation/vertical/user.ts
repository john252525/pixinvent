export default [
  {
    heading: 'User Dashboard',
    subject: 'user',
    action:'read',
  },
  {
    title: 'Accounts',
    icon: { icon: 'mdi-accounts-switch' },
    to: 'accounts',
    subject: 'accounts',
    action: 'read',
  },
  {
    title: 'Settings',
    icon: { icon: 'mdi:settings-outline' },
    to: { name: 'settings' },
    subject: 'settings',
    action: 'read',
  },
]
