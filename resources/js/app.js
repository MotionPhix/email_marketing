import './bootstrap';
import '../css/app.css';

import {createApp, h} from 'vue';
import {createInertiaApp} from '@inertiajs/vue3';
import {ZiggyVue} from '../../vendor/tightenco/ziggy';
import {ModalLink, renderApp} from '@inertiaui/modal-vue'
import VueApexCharts from "vue3-apexcharts";
import {createPinia} from "pinia";

import { install as VueMonacoEditorPlugin } from '@guolao/vue-monaco-editor'
import {setupCalendar, Calendar as VCalendar, DatePicker} from 'v-calendar';
import 'v-calendar/style.css';

// Existing components
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
  DropdownMenuShortcut,
  DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu'

// Additional shadcn components useful for billing
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from "@/Components/ui/dialog"

import {
  Alert,
  AlertDescription,
  AlertTitle,
} from "@/Components/ui/alert"

import {
  Avatar,
  AvatarImage,
  AvatarFallback,
} from "@/Components/ui/avatar"

import {
  Toast,
  ToastAction,
  ToastClose,
  ToastDescription,
  ToastProvider,
  ToastTitle,
  ToastViewport,
} from "@/Components/ui/toast"

import {
  Tabs,
  TabsList,
  TabsContent,
  TabsTrigger,
} from "@/Components/ui/tabs"

import {
  Sheet,
  SheetContent,
  SheetHeader,
  SheetTitle,
  SheetTrigger,
  SheetDescription
} from '@/Components/ui/sheet'

import {Badge} from "@/Components/ui/badge"
import {Checkbox} from "@/Components/ui/checkbox"
import {Progress} from "@/Components/ui/progress"
import {Separator} from "@/Components/ui/separator"
import {Label} from "@/Components/ui/label"
import {Button} from "@/Components/ui/button"
import {Input} from "@/Components/ui/input"
import {Calendar} from "@/Components/ui/v-calendar"
import FormField from "@/Components/Forms/FormField.vue";
import GlobalModal from "@/Components/GlobalModal.vue";
import {resolvePageComponent} from "laravel-vite-plugin/inertia-helpers";

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';
const pinia = createPinia()

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
  setup({el, App, props, plugin}) {
    return createApp({render: renderApp(App, props)})
      .use(plugin)
      .use(ZiggyVue)
      .use(pinia)
      .use(VueApexCharts)
      .use(setupCalendar, {})
      .use(VueMonacoEditorPlugin, {
        paths: {
          // The recommended CDN config
          vs: 'https://cdn.jsdelivr.net/npm/monaco-editor@0.43.0/min/vs'
        },
      })

      // Existing components
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
      .component('DropdownMenuShortcut', DropdownMenuShortcut)
      .component('FormField', FormField)
      .component('Checkbox', Checkbox)
      .component('Calendar', Calendar)
      .component('Button', Button)
      .component('Input', Input)
      .component('Label', Label)

      // New shadcn components
      .component('Sheet', Sheet)
      .component('SheetContent', SheetContent)
      .component('SheetHeader', SheetHeader)
      .component('SheetTitle', SheetTitle)
      .component('SheetTrigger', SheetTrigger)
      .component('SheetDescription', SheetDescription)
      .component('Dialog', Dialog)
      .component('DialogContent', DialogContent)
      .component('DialogDescription', DialogDescription)
      .component('DialogFooter', DialogFooter)
      .component('DialogHeader', DialogHeader)
      .component('DialogTitle', DialogTitle)
      .component('DialogTrigger', DialogTrigger)
      .component('Alert', Alert)
      .component('AlertDescription', AlertDescription)
      .component('AlertTitle', AlertTitle)
      .component('Toast', Toast)
      .component('ToastAction', ToastAction)
      .component('ToastClose', ToastClose)
      .component('ToastDescription', ToastDescription)
      .component('ToastProvider', ToastProvider)
      .component('ToastTitle', ToastTitle)
      .component('ToastViewport', ToastViewport)
      .component('Badge', Badge)
      .component('Progress', Progress)
      .component('Separator', Separator)
      .component('Tabs', Tabs)
      .component('TabsTrigger', TabsTrigger)
      .component('TabsList', TabsList)
      .component('TabsContent', TabsContent)
      .component('Avatar', Avatar)
      .component('AvatarImage', AvatarImage)
      .component('AvatarFallback', AvatarFallback)
      .mount(el);
  },
  progress: {
    color: '#4B5563',
  },
});
