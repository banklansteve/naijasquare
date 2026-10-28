import '../css/app.css';

import { createInertiaApp } from '@inertiajs/vue3';
import type { DefineComponent } from 'vue';
import { createApp, h } from 'vue';

import { appName } from '@/lib/app';

const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue');

createInertiaApp({
    title: (title) => (title ? `${title} · ${appName}` : appName),
    resolve: async (name) => {
        const page = pages[`./pages/${name}.vue`];

        if (!page) {
            throw new Error(`Page not found: ${name}`);
        }

        return page();
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    progress: {
        color: '#B56B3C',
    },
});
