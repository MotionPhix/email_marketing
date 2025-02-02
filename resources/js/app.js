import './bootstrap';
import 'maz-ui/styles'
import '../css/app.css';

import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/vue3';
import {resolvePageComponent} from 'laravel-vite-plugin/inertia-helpers';
import {ZiggyVue} from '../../vendor/tightenco/ziggy';
import {ModalLink, renderApp} from '@inertiaui/modal-vue'
import {createPinia} from "pinia";
import {installToaster} from 'maz-ui'
import VueApexCharts from "vue3-apexcharts";

import { setupCalendar, Calendar as VCalendar, DatePicker } from 'v-calendar';
import 'v-calendar/style.css';

// components
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  CardFooter,
} from '@/Components/ui/card'

import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectLabel,
  SelectValue,
  SelectGroup,
  SelectSeparator,
} from "@/Components/ui/select";
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuGroup,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'
import {Label} from "@/Components/ui/label"
import {Button} from "@/Components/ui/button"
import {Input} from "@/Components/ui/input"
import {Calendar} from "@/Components/ui/v-calendar"
import FormField from "@/Components/Forms/FormField.vue";
import GlobalModal from "@/Components/GlobalModal.vue";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia()

// DEFAULT OPTIONS
const toasterOptions = {
  position: 'bottom-right',
  timeout: 10_000,
  persistent: false,
}

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
      .use(setupCalendar, {})
      .component('VCalendar', VCalendar)
      .component('VDatePicker', DatePicker)
      .component('GlobalModal', GlobalModal)
      .component('GlobalLink', ModalLink)
      .component('Card', Card)
      .component('CardContent', CardContent)
      .component('CardDescription', CardDescription)
      .component('CardHeader', CardHeader)
      .component('CardTitle', CardTitle)
      .component('CardFooter', CardFooter)
      .component('Select', Select)
      .component('SelectContent', SelectContent)
      .component('SelectItem', SelectItem)
      .component('SelectTrigger', SelectTrigger)
      .component('SelectLabel', SelectLabel)
      .component('SelectValue', SelectValue)
      .component('SelectGroup', SelectGroup)
      .component('SelectSeparator', SelectSeparator)
      .component('DropdownMenu', DropdownMenu)
      .component('DropdownMenuContent', DropdownMenuContent)
      .component('DropdownMenuGroup', DropdownMenuGroup)
      .component('DropdownMenuItem', DropdownMenuItem)
      .component('DropdownMenuLabel', DropdownMenuLabel)
      .component('DropdownMenuSeparator', DropdownMenuSeparator)
      .component('DropdownMenuTrigger', DropdownMenuTrigger)
      .component('FormField', FormField)
      .component('Calendar', Calendar)
      .component('Button', Button)
      .component('Input', Input)
      .component('Label', Label)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
