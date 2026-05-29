const UserProfile = {

    init() {

    },

    openSignatureChoiceModal() {
        document.getElementById(
            'signatureChoiceModal'
        ).style.display = 'flex';
    },

    closeSignatureChoiceModal() {
        document.getElementById(
            'signatureChoiceModal'
        ).style.display = 'none';
    },

    openSignatureUploadModal() {

        this.closeSignatureChoiceModal();

        document.getElementById(
            'signatureUploadModal'
        ).style.display = 'flex';
    },

    closeSignatureUploadModal() {
        document.getElementById(
            'signatureUploadModal'
        ).style.display = 'none';
    },

    openSignatureDrawModal() {

        this.closeSignatureChoiceModal();

        document.getElementById(
            'signatureDrawModal'
        ).style.display = 'flex';
    },

    closeSignatureDrawModal() {
        document.getElementById(
            'signatureDrawModal'
        ).style.display = 'none';
    }

};

window.openSignatureChoiceModal =
    () => UserProfile.openSignatureChoiceModal();

window.closeSignatureChoiceModal =
    () => UserProfile.closeSignatureChoiceModal();

window.openSignatureUploadModal =
    () => UserProfile.openSignatureUploadModal();

window.closeSignatureUploadModal =
    () => UserProfile.closeSignatureUploadModal();

window.openSignatureDrawModal =
    () => UserProfile.openSignatureDrawModal();

window.closeSignatureDrawModal =
    () => UserProfile.closeSignatureDrawModal();

export default UserProfile;