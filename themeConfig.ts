import { breakpointsVuetifyV3, rand } from '@vueuse/core'
import { VIcon } from 'vuetify/components/VIcon'
import { defineThemeConfig } from '@core'
import { Skins } from '@core/enums'

// ❗ Logo SVG must be imported with ?raw suffix
import logo from '@images/logo.svg?raw'

import { AppContentLayoutNav, ContentWidth, FooterType, NavbarType } from '@layouts/enums'

const hostname = window.location.hostname;
let title = '';

if (hostname === import.meta.env.VITE_DOMAIN_SETTINGS) {
  title = import.meta.env.VITE_SETTINGS_NAME;
}

if (hostname === import.meta.env.VITE_DOMAIN_ACCOUNTS) {
  title = import.meta.env.VITE_ACCOUNTS_NAME;
}

if (hostname === import.meta.env.VITE_DOMAIN_BINDER) {
  title = import.meta.env.VITE_BINDER_NAME;
}

if (hostname === import.meta.env.VITE_DOMAIN_RESERVED) {
  title = import.meta.env.VITE_RESERVED_NAME;
}

export const { themeConfig, layoutConfig } = defineThemeConfig({
  app: {
    title: title as Lowercase<string>,
    logo: h('div', { innerHTML: logo, style: 'line-height:0; color: rgb(var(--v-global-theme-primary))' }),
    contentWidth: ContentWidth.Boxed,
    contentLayoutNav: AppContentLayoutNav.Vertical,
    overlayNavFromBreakpoint: breakpointsVuetifyV3.lg - 1, // 1 for matching with vuetify breakpoint. Docs: https://next.vuetifyjs.com/en/features/display-and-platform/
    i18n: {
      enable: true,
      defaultLocale: localStorage.getItem('locale') || 'en',
      langConfig: [
        {
          label: 'Русский',
          flag: 'ru',
          i18nLang: 'ru',
          isRTL: false,
        },
        {
          label: 'English',
          flag: 'us',
          i18nLang: 'en',
          isRTL: false,
        },
      ],
    },
    theme: 'system',
    skin: Skins.Default,
    iconRenderer: VIcon,
  },
  navbar: {
    type: NavbarType.Sticky,
    navbarBlur: true,
  },
  footer: { type: FooterType.Static },
  verticalNav: {
    isVerticalNavCollapsed: false,
    defaultNavItemIconProps: { icon: 'tabler-circle' },
    isVerticalNavSemiDark: false,
  },
  horizontalNav: {
    type: 'sticky',
    transition: 'slide-y-reverse-transition',
    popoverOffset: 6,
  },

  /*
  // ℹ️  In below Icons section, you can specify icon for each component. Also you can use other props of v-icon component like `color` and `size` for each icon.
  // Such as: chevronDown: { icon: 'tabler-chevron-down', color:'primary', size: '24' },
  */
  icons: {
    chevronDown: { icon: 'tabler-chevron-down' },
    chevronRight: { icon: 'tabler-chevron-right', size: 20 },
    close: { icon: 'tabler-x', size: 20 },
    verticalNavPinned: { icon: 'tabler-circle-dot', size: 20 },
    verticalNavUnPinned: { icon: 'tabler-circle', size: 20 },
    sectionTitlePlaceholder: { icon: 'tabler-minus' },
  },
})
