import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
import axiosPlugin from '@/app/plugins/axios';
import apiPlugin from '@/app/plugins/api';
import echoPlugin, { userIdFromProps } from '@/app/plugins/echo';
import { vLoading } from '@/shared/lib/directives/loading';
import '@/shared/styles/app.scss';

createInertiaApp({
    title: (title) => (title ? `${title} — eProduct Studio` : 'eProduct Studio'),
    resolve: (name) =>
        resolvePageComponent(
            `../../pages/${name}.vue`,
            import.meta.glob<DefineComponent>('../../pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin);
        app.use(PrimeVue, {
            theme: {
                preset: Aura,
            },
        });

        app.use(axiosPlugin);
        app.use(apiPlugin);
        app.use(echoPlugin, { initialUserId: userIdFromProps(props.initialPage.props) });

        app.directive('loading', vLoading);

        app.mount(el);
    },
});
