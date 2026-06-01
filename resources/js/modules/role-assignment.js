export function initRoleAssignmentForm() {
    const middleNameInput = document.getElementById('middle_name');
    const noMiddleNameCheckbox = document.getElementById('no_middle_name');

    if (!middleNameInput || !noMiddleNameCheckbox) {
        return;
    }

    function toggleMiddleName() {
        if (noMiddleNameCheckbox.checked) {
            middleNameInput.value = '';
            middleNameInput.disabled = true;
            middleNameInput.classList.add('input-disabled');
        } else {
            middleNameInput.disabled = false;
            middleNameInput.classList.remove('input-disabled');
        }
    }

    noMiddleNameCheckbox.addEventListener('change', toggleMiddleName);

    toggleMiddleName();
}