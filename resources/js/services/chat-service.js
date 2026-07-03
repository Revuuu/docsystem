import axios from 'axios';

const ChatService = {
    async getMessages(documentId) {
        const { data } =
            await axios.get(
                `/documents/${documentId}/messages`
            );

        return data;
    },

    async sendMessage(documentId, message) {
        const { data } =
            await axios.post(
                `/documents/${documentId}/messages`,
                { message }
            );

        return data;
    },
};

export default ChatService;