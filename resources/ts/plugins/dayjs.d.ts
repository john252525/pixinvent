import dayjs from 'dayjs'
import isLeapYear from 'dayjs/plugin/isLeapYear'
import relativeTime from 'dayjs/plugin/relativeTime'
import LocalizedFormat from 'dayjs/plugin/localizedFormat'
import isSameOrBefore from 'dayjs/plugin/isSameOrBefore'
import isSameOrAfter from 'dayjs/plugin/isSameOrAfter'
import duration from 'dayjs/plugin/duration'
import utc from 'dayjs/plugin/utc'
import 'dayjs/locale/ru'

export default function (app) {
  dayjs.extend(isSameOrBefore)
  dayjs.extend(isSameOrAfter)
  dayjs.extend(relativeTime)
  dayjs.extend(LocalizedFormat)
  dayjs.extend(isLeapYear)
  dayjs.extend(duration)
  dayjs.extend(utc)
  dayjs.locale(localStorage.getItem('locale', 'en'))

  app.config.globalProperties.$dayjs = dayjs
  app.provide('dayjs', dayjs)

  app.use(dayjs)
}
