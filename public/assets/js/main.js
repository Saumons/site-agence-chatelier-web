document.addEventListener('DOMContentLoaded', () => {
    const burger = document.querySelector('.burger');
    const nav = document.querySelector('.main-nav');

    if (!burger || !nav) {
        return;
    }

    burger.addEventListener('click', () => {
        const isOpen = nav.classList.toggle('is-open');
        burger.setAttribute('aria-expanded', String(isOpen));
    });
});
