/**
 * global type definitions
 * using the typescript interface, you can define the i18n resources that is type-safed!
 */

/**
 * you need to import the some interfaces
 */
import en from '@/plugins/i18n/locales/en.json';
import ru from '@/plugins/i18n/locales/ru.json';
import 'vue-i18n';

type LocaleMessage = typeof en | typeof ru;

declare module 'vue-i18n' {
  export interface DefineLocaleMessage extends LocaleMessage {

  }
}
