import { Server } from 'socket.io';

import DocumentChannel from './channels/document.channel.js';
import UserChannel from './channels/user.channel.js';
import Logger from './services/logger.service.js';

export default function createSocketServer(server, corsOptions) {
    const io = new Server(server, {
        cors: corsOptions,
    });

    io.on('connection', (socket) => {
        Logger.info(`Connected ${socket.id}`);

        DocumentChannel(io, socket);
        UserChannel(io, socket);

        socket.on('disconnect', () => {
            Logger.info(`Disconnected ${socket.id}`);
        });
    });

    return io;
}