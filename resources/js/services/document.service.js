import Api from './api.service';

const DocumentService = {

    search(params)
    {
        return Api.get(
            window.location.pathname +
            '?' +
            new URLSearchParams(params)
        );
    }
};

export default DocumentService;