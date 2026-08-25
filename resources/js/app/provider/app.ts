import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { DefineComponent } from 'vue';
import PrimeVue from 'primevue/config';
import Aura from '@primeuix/themes/aura';
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

        app.mount(el);
    },
});