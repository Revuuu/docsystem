const Approver = {

    init()
    {
        this.container =
            document.getElementById(
                'approver-container'
            );
    },

    add()
    {
        if (!this.container) return;

        const firstRow =
            this.container.querySelector(
                '.approver-row'
            );

        if (!firstRow) return;

        const clone =
            firstRow.cloneNode(true);

        const select =
            clone.querySelector('select');

        if (select) {
            select.value = '';
        }

        this.container.appendChild(
            clone
        );
    }
};

window.addApprover =
    () => Approver.add();

export default Approver;