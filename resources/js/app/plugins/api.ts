import { inject, type App as VueApp } from 'vue';
import { apiKey, axiosKey, createApi, type Api } from '@/shared/api';

export default {
    install(app: VueApp): void {
        const http = app.runWithContext(() => inject(axiosKey));

        if (!http) {
            throw new Error('api plugin требует axios plugin: вызовите app.use(axiosPlugin) раньше');
        }

        const api = createApi(http);

        app.provide(apiKey, api);
        app.config.globalProperties.$api = api;
    },
};

declare module 'vue' {
    interface ComponentCustomProperties {
        $api: Api;
    }
}
