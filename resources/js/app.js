import '../css/app.css';
import 'vue-sonner/style.css';
import './bootstrap';

import AppLayout from '@/Layouts/AppLayout.vue';
import AuthLayout from '@/Layouts/AuthLayout.vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, Fragment } from 'vue';
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
        const app = createApp({
            render: () => h(Fragment, null, [
                h(App, props),
                h(Toaster, {
                    position: 'top-right',
                    richColors: true,
                    closeButton: true,
                    expand: true,
                    offset: '1rem',
                }),
            ]),
        });

        app.component('Head', Head);
        app.component('Link', Link);

        return app
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: 'hsl(var(--primary))',
    },
});
