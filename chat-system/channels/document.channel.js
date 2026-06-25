import DocumentBroadcast from '../broadcasts/document.broadcast.js';
import Logger from '../services/logger.service.js';

export default function DocumentChannel(io, socket) {
    socket.on('join-document-chat', (documentId) => {
        socket.join(`document-${documentId}`);

        Logger.info(`${socket.id} joined document-${documentId}`);
    });

    socket.on('leave-document-chat', (documentId) => {
        socket.leave(`document-${documentId}`);

        Logger.info(`${socket.id} left document-${documentId}`);
    });

    socket.on('send-document-message', (payload) => {
        DocumentBroadcast.sendMessage(io, payload);
    });
}