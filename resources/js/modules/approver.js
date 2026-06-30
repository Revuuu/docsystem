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
        
        const removeButton =
        clone.querySelector(
            '.btn-remove-approver'
        );

        if (removeButton) {
            removeButton.style.display = 'flex';
        }

        this.container.appendChild(
            clone
        );
    },
    remove(button)
    {
        if (!this.container) return;

       
        if (
            this.container.querySelectorAll(
                '.approver-row'
            ).length <= 1
        ) {
            return;
        }

        button
        .closest('.approver-row')
        ?.remove();
    }
};

window.addApprover =
    () => Approver.add();

window.removeApprover =
    (button) => Approver.remove(button);

export default Approver;