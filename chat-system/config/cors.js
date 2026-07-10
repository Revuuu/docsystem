export default function createCorsOptions(appUrl) {
    if (!appUrl) {
        throw new Error('APP_URL is missing from the .env file.');
    }

    const allowedOrigin = appUrl.replace(/\/+$/, '');

    return {
        origin: [
            allowedOrigin,
        ],

        methods: [
            'GET',
            'POST',
        ],

        credentials: true,
    };
}