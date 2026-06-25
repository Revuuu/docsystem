import Logger from '../services/logger.service.js';

export default function UserChannel(io, socket) {
    socket.on('join-user-room', (userId) => {
        socket.join(`user-${userId}`);

        Logger.info(`${socket.id} joined user-${userId}`);
    });
}