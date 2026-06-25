module.exports = {

    sendNotification(io, userId, payload) {

        io.to(
            `user-${userId}`
        ).emit(
            'user-notification',
            payload
        );

    }

};