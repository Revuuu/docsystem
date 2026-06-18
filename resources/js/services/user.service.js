import Api from './api.service';

const UserService = {

    search(params)
    {
        return Api.get(
            window.location.pathname +
            '?' +
            new URLSearchParams(params)
        );
    }
};

export default UserService;