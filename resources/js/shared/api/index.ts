import { inject, type InjectionKey } from 'vue';
import type { AxiosInstance } from 'axios';
import { createProductAiApi } from './product-ai';

export function createApi(http: AxiosInstance) {
    return {
        productAi: createProductAiApi(http),
    };
}

export type Api = ReturnType<typeof createApi>;

export const axiosKey: InjectionKey<AxiosInstance> = Symbol('axios');
export const apiKey: InjectionKey<Api> = Symbol('api');

export function useApi(): Api {
    const api = inject(apiKey);

    if (!api) {
        throw new Error('$api не установлен: подключите api plugin в app.ts');
    }

    return api;
}
