const Api = {

    async get(url)
    {
        const response =
            await fetch(url);

        return response.text();
    },

    async post(url, data)
    {
        const response =
            await fetch(url, {
                method: 'POST',
                body: data,
                headers: {
                    'X-Requested-With':
                        'XMLHttpRequest'
                }
            });

        return response.json();
    }
};

export default Api;