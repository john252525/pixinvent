export default [
  {
    heading: 'User Dashboard',
    action: 'read',
    subject: 'user',
  },
  {
    title: 'Home',
    icon: { icon: 'mdi-home' },
    to: 'first-page',
    action: 'read',
    subject: 'user',
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
    action: 'manage',
    subject: 'all',
  },
]
