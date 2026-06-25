export default {
    sendMessage(io, payload) {
        io.to(`document-${payload.document_id}`)
            .emit('receive-document-message', payload);
    },
};