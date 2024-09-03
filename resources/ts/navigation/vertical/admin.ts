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
      {
        title: 'Level 2.2',
        children: [
          {
            title: 'Level 3.1',
            to: null,
          },
          {
            title: 'Level 3.2',
            to: null,
          },
        ],
      },
    ],
  },
]
