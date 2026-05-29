const RejectModal = {

    init() {},

    open(id)
    {
        const modal =
            document.getElementById(
                'rejectModal'
            );

        const form =
            document.getElementById(
                'rejectForm'
            );

        if (!modal || !form) return;

        form.action =
            `/approvals/${id}/reject`;

        modal.style.display = 'flex';
    },

    close()
    {
        document
            .getElementById(
                'rejectModal'
            )
            ?.style
            .setProperty(
                'display',
                'none'
            );
    }
};

window.openRejectModal =
    (id) => RejectModal.open(id);

window.closeRejectModal =
    () => RejectModal.close();

export default RejectModal;