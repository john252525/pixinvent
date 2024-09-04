export default [
  {
    heading: 'Payments',
    subject: 'user',
    action: 'read',
  },
  {
    title: 'Price list',
    icon: { icon: 'game-icons:pay-money' },
    to: { name: 'payments-pricing' },
    subject: 'user',
    action: 'read',
  },
  {
    title: 'YooKassa',
    icon: { icon: 'brandico:youku' },
    to: { name: 'payments-yoo-kassa' },
    subject: 'user',
    action: 'read',
  },
]
