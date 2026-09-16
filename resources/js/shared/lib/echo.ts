import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
import type { AxiosInstance } from 'axios';

let echo: Echo<'reverb'> | null = null;
let connectedUserId: number | null = null;

export function connectEcho(userId: number, http: AxiosInstance): Echo<'reverb'> {
    if (echo && connectedUserId === userId) {
        return echo;
    }

    disconnectEcho();

    echo = new Echo({
        broadcaster: 'reverb',
        Pusher,
        key: import.meta.env.VITE_REVERB_APP_KEY,
        wsHost: import.meta.env.VITE_REVERB_HOST,
        wsPort: Number(import.meta.env.VITE_REVERB_PORT ?? 80),
        wssPort: Number(import.meta.env.VITE_REVERB_PORT ?? 443),
        forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
        enabledTransports: ['ws', 'wss'],
        channelAuthorization: {
            endpoint: '/broadcasting/auth',
            transport: 'ajax',
            customHandler: ({ socketId, channelName }, callback) => {
                http.post('/broadcasting/auth', { socket_id: socketId, channel_name: channelName })
                    .then((response) => callback(null, response.data))
                    .catch((error: Error) => callback(error, null));
            },
        },
    });

    connectedUserId = userId;

    return echo;
}

export function getEcho(): Echo<'reverb'> | null {
    return echo;
}

export function disconnectEcho(): void {
    echo?.disconnect();
    echo = null;
    connectedUserId = null;
}

export function subscribeToUserChannel<TPayload>(
    userId: number,
    event: string,
    handler: (payload: TPayload) => void,
): () => void {
    const channel = echo?.private(`App.Models.User.${userId}`);

    if (!channel) {
        return () => {};
    }

    channel.listen(event, handler);

    return () => {
        channel.stopListening(event, handler);
    };
}
