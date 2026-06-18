import '../css/app.css';
import './bootstrap';

import AppLayout from '@/Layouts/AppLayout.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { Toaster } from 'vue-sonner';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Linh Ta Linh Tinh';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        const page = await resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        );

        if (name.startsWith('Auth/')) {
            page.default.layout = page.default.layout ?? AuthLayout;
        } else if (!name.startsWith('Profile/Partials/')) {
            page.default.layout = page.default.layout ?? AppLayout;
        }

        return page;
    },
    setup({ el, App, props, plugin }) {
        return createApp({
            render: () => h('div', [
                h(App, props),
                h(Toaster, { position: 'top-right', richColors: true }),
            ]),
        })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: 'hsl(var(--primary))',
    },
});
