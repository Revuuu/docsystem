import express from 'express';
import http from 'http';

import { loadEnv } from 'vite';
import { dirname, resolve } from 'path';
import { fileURLToPath } from 'url';

import createCorsOptions from './config/cors.js';
import createSocketServer from './socket.js';

const __filename = fileURLToPath(import.meta.url);
const __dirname = dirname(__filename);

const projectRoot = resolve(__dirname, '..');
const mode = process.env.NODE_ENV || 'development';

const env = loadEnv(mode, projectRoot, '');

const APP_URL = env.APP_URL;
const SOCKET_HOST = env.SOCKET_HOST || '0.0.0.0';
const SOCKET_PORT = Number(env.SOCKET_PORT || 3000);

if (!APP_URL) {
    throw new Error(
        `APP_URL is missing from ${resolve(projectRoot, '.env')}`
    );
}

if (!Number.isInteger(SOCKET_PORT) || SOCKET_PORT <= 0) {
    throw new Error('SOCKET_PORT must be a valid port number.');
}

const app = express();
const server = http.createServer(app);

const corsOptions = createCorsOptions(APP_URL);

app.get('/', (req, res) => {
    res.json({
        status: 'running',
        socketPort: SOCKET_PORT,
        allowedOrigin: APP_URL.replace(/\/+$/, ''),
    });
});

createSocketServer(
    server,
    corsOptions
);

server.listen(SOCKET_PORT, SOCKET_HOST, () => {
    console.log(
        `Realtime server running on ${SOCKET_HOST}:${SOCKET_PORT}`
    );

    console.log(
        `Network URL: http://172.16.0.112:${SOCKET_PORT}`
    );

    console.log(
        `Allowed origin: ${APP_URL.replace(/\/+$/, '')}`
    );
});