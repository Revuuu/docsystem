import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import os from 'os';

function getLocalIP() {
    const interfaces = os.networkInterfaces();

    for (const name of Object.keys(interfaces)) {
        for (const iface of interfaces[name]) {
            if (
                iface.family === 'IPv4' &&
                !iface.internal
            ) {
                return iface.address;
            }
        }
    }

    return 'localhost';
}

const localIP = getLocalIP();

export default defineConfig({
    plugins: [
        laravel([
            'resources/css/app.css',
            'resources/js/app.js',
        ]),
    ],

    server: {
        host: '0.0.0.0',
        port: 5173,
        cors: true,

        hmr: {
            host: localIP,
        },
    },
});