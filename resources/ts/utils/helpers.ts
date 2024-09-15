export const toMoney = (value: number) => {
  return (value / 100).toLocaleString('ru-RU', { style: 'currency', currency: 'RUB' })
}
