import './bootstrap';
import 'maz-ui/styles'
import '../css/app.css';

import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy';
import {Modal, ModalLink, putConfig, renderApp} from '@inertiaui/modal-vue'
import {createPinia} from "pinia";
import { installToaster } from 'maz-ui'
import VueApexCharts from "vue3-apexcharts";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia()

// DEFAULT OPTIONS
const toasterOptions = {
  position: 'bottom-right',
  timeout: 10_000,
  persistent: false,
}

putConfig({
  modal: {
    closeButton: true,
    closeExplicitly: true,
    position: 'top',
  },
  slideover: {
    closeButton: false,
    closeExplicitly: true,
    position: 'right',
  },
})

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({el, App, props, plugin}) {
    return createApp({render: renderApp(App, props)})
      .use(plugin)
      .use(ZiggyVue)
      .use(pinia)
      .use(VueApexCharts)
      .use(installToaster, toasterOptions)
      .component('GlobalModal', Modal)
      .component('GlobalLink', ModalLink)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
