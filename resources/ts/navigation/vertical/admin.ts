export default [
  {
    heading: 'Admin Dashboard',
    subject: 'admin',
    action: 'read',
  },
  {
    title: 'User management',
    icon: { icon: 'tabler-users' },
    subject: 'admin',
    action: 'read',
    children: [
      {
        title: 'User list',
        to: { name: 'admin-users-list' },
        subject: 'admin',
        action: 'read',
      },
    ],
  },
  {
    title: 'Access management',
    icon: { icon: 'mdi-user-access-control' },
    subject: 'admin',
    action: 'read',
    children: [
      {
        title: 'Roles',
        to: { name: 'admin-roles-list' },
        subject: 'admin',
        action: 'read',
      },
      {
        title: 'Permissions',
        to: { name: 'admin-permissions-list' },
        subject: 'admin',
        action: 'read',
      },
    ],
  },
  {
    title: 'Payments management',
    icon: { icon: 'mdi-account-payment-outline' },
    subject: 'admin',
    action: 'read',
    children: [
      {
        title: 'Transactions',
        to: { name: 'admin-payments-transactions' },
        subject: 'admin',
        action: 'read',
      },
    ],
  },
  {
    title: 'navigation.admin.logs',
    icon: { icon: 'icon-park-outline:upload-logs' },
    to: { name: 'admin-logs' },
    subject: 'admin',
    action: 'read',
  },
]
