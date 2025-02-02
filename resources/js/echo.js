import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
import {usePage} from "@inertiajs/vue3";
window.Pusher = Pusher;

window.Echo = new Echo({
  broadcaster: 'pusher',
  key: import.meta.env.VITE_PUSHER_APP_KEY,
  cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
  forceTLS: true,
});

const page = usePage()

window.Echo.private(`campaign.stats.1`)
  .listen('.stats.updated', (e) => {
    console.log(e)
  })
