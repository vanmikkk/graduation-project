import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import VueGtag from "vue-gtag";
import VueYandexMetrika from 'vue-yandex-metrika';

createApp(App).use(router).mount('#app')

// app.use(VueGtag, {
//   config: { id: "G-0PKSC9487H" } 
// }, router);

// app.use(VueYandexMetrika, {
//     id: 107948878,
//     router: router,
//     env: process.env.NODE_ENV, 
//     options: {
//         clickmap: true,
//         trackLinks: true,
//         accurateTrackBounce: true,
//         webvisor: true
//     }
// });

// app.use(router);
// app.mount('#app');
