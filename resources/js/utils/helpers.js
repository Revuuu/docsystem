export const show = (el) => {
    if (el) el.style.display = 'block';
};

export const hide = (el) => {
    if (el) el.style.display = 'none';
};

export const toggleClass = (
    el,
    className
) => {

    if (!el) return;

    el.classList.toggle(className);
};