import axios, { type AxiosInstance } from 'axios';
import type { App as VueApp } from 'vue';
import { axiosKey } from '@/shared/api';

export function createHttp(): AxiosInstance {
    return axios.create({
        withCredentials: true,
        withXSRFToken: true,
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });
}

export default {
    install(app: VueApp): void {
        const http = createHttp();

        app.provide(axiosKey, http);
        app.config.globalProperties.$axios = http;
    },
};

declare module 'vue' {
    interface ComponentCustomProperties {
        $axios: AxiosInstance;
    }
}
