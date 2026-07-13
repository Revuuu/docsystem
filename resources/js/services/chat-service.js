import axios from 'axios';

const ChatService = {
    async getMessages(documentId) {
        const { data } =
            await axios.get(
                `/documents/${documentId}/messages`
            );

        if (Array.isArray(data)) {
            return data;
        }

        if (Array.isArray(data?.data)) {
            return data.data;
        }

        return [];
    },

    async sendMessage(
        documentId,
        message = '',
        attachment = null
    ) {
        const formData = new FormData();

        const cleanMessage =
            String(message ?? '').trim();

        if (cleanMessage !== '') {
            formData.append(
                'message',
                cleanMessage
            );
        }

        if (attachment instanceof File) {
            formData.append(
                'attachment',
                attachment
            );
        }

        const { data } =
            await axios.post(
                `/documents/${documentId}/messages`,
                formData,
                {
                    headers: {
                        Accept: 'application/json',
                    },
                }
            );

        return data?.data ?? data;
    },
};

export default ChatService;