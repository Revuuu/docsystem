import Api from './api.service';

const ApprovalService = {

    approve(id, formData)
    {
        return Api.post(
            `/approvals/${id}/approve`,
            formData
        );
    },

    reject(id, formData)
    {
        return Api.post(
            `/approvals/${id}/reject`,
            formData
        );
    }
};

export default ApprovalService;