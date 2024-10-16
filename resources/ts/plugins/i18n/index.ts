import type { App } from 'vue'
import { createI18n, useI18n } from 'vue-i18n'
import { cookieRef } from '@layouts/stores/config'
import { themeConfig } from '@themeConfig'

const messages = Object.fromEntries(
  Object.entries(
    import.meta.glob<{ default: any }>('./locales/*.json', { eager: true }))
    .map(([key, value]) => [key.slice(10, -5), value.default]),
)

let _i18n: any = null

export const getI18n = () => {
  if (_i18n === null) {
    _i18n = createI18n({
      legacy: false,
      locale: cookieRef('language', themeConfig.app.i18n.defaultLocale).value,
      fallbackLocale: 'en',
      messages,
      // Key - language to use the rule for, `'ru'`, in this case
      // Value - function to choose right plural form
      pluralizationRules: {
        /**
         * @param choice {number} a choice index given by the input to $tc: `$tc('path.to.rule', choiceIndex)`
         * @param choicesLength {number} an overall amount of available choices
         * @returns a final choice index to select plural word by
         */
        'ru': function(choice, choicesLength) {
          // this === VueI18n instance, so the locale property also exists here

          if (choice === 0) {
            return 0;
          }

          const teen = choice > 10 && choice < 20;
          const endsWithOne = choice % 10 === 1;

          if (choicesLength < 4) {
            return (!teen && endsWithOne) ? 1 : 2;
          }
          if (!teen && endsWithOne) {
            return 1;
          }
          if (!teen && choice % 10 >= 2 && choice % 10 <= 4) {
            return 2;
          }

          return (choicesLength < 4) ? 2 : 3;
        }
      }
    })
  }
  return _i18n
}

export const globalI18n = () => {
  const { global } = getI18n()

  return global
}

export default function (app: App) {
  app.use(getI18n())
}
