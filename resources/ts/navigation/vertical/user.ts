export default [
  {
    heading: 'User Dashboard',
  },
  {
    title: 'Accounts',
    icon: { icon: 'mdi-accounts-switch' },
    to: 'accounts',
  },
  {
    title: 'Limited access',
    icon: { icon: 'tabler-command' },
    to: 'access-control',
    action: 'read',
    subject: 'premium',
  },
  {
    title: 'Documentation',
    href: 'https://demos.pixinvent.com/vuexy-vuejs-admin-template/documentation/guide/laravel-integration/folder-structure.html',
    icon: { icon: 'tabler-file-text' },
    target: '_blank',
  },
]
