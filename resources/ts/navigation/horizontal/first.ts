export default [
  {
    title: 'Misc',
    icon: { icon: 'tabler-box-multiple' },
    children: [
      {
        title: 'Главная',
        icon: { icon: 'mdi-home' },
        to: 'first',
        action: 'read',
        subject: 'AclDemo',
      },
      {
        title: 'Контроль доступа',
        icon: { icon: 'tabler-command' },
        to: 'access-control',
        action: 'read',
        subject: 'AclDemo',
      },
      {
        title: 'Documentation',
        href: 'https://demos.pixinvent.com/vuexy-vuejs-admin-template/documentation/',
        icon: { icon: 'tabler-file-text' },
        target: '_blank',
      },
    ],
  },
]
