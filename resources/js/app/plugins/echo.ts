import { inject, type App as VueApp } from 'vue';
import { router } from '@inertiajs/vue3';
import { axiosKey } from '@/shared/api';
import { connectEcho, disconnectEcho } from '@/shared/lib/echo';

type EchoPluginOptions = {
    initialUserId: number | null;
};

export function userIdFromProps(props: unknown): number | null {
    const auth = (props as { auth?: { user?: { id: number } | null } }).auth;

    return auth?.user?.id ?? null;
}

export default {
    install(app: VueApp, options: EchoPluginOptions): void {
        const http = app.runWithContext(() => inject(axiosKey));

        if (!http) {
            throw new Error('echo plugin требует axios plugin: вызовите app.use(axiosPlugin) раньше');
        }

        const sync = (userId: number | null): void => {
            if (userId === null) {
                disconnectEcho();

                return;
            }

            connectEcho(userId, http);
        };

        sync(options.initialUserId);

        router.on('navigate', (event) => sync(userIdFromProps(event.detail.page.props)));
    },
};
