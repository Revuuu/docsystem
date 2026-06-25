import express from 'express';
import http from 'http';

import corsOptions from './config/cors.js';
import createSocketServer from './socket.js';

const app = express();

const server = http.createServer(app);

app.get('/', (req, res) => {
    res.json({
        status: 'running'
    });
});

createSocketServer(
    server,
    corsOptions
);

const PORT = 3000;

server.listen(PORT, () => {
    console.log(
        `Realtime server running on port ${PORT}`
    );
});