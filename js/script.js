const roleCards = document.querySelectorAll('.role-card');

roleCards.forEach((card) => {
    card.addEventListener('pointermove', (event) => {
        const rect = card.getBoundingClientRect();
        const x = event.clientX - rect.left;
        const y = event.clientY - rect.top;

        card.style.background = `
            radial-gradient(circle at ${x}px ${y}px, rgba(15, 118, 110, 0.12), transparent 130px),
            #ffffff
        `;
    });

    card.addEventListener('pointerleave', () => {
        card.style.background = '#ffffff';
    });
});

document.querySelectorAll('a[href*="#"]').forEach((link) => {
    link.addEventListener('click', (event) => {
        const url = new URL(link.getAttribute('href'), window.location.href);

        if (url.pathname !== window.location.pathname || !url.hash) {
            return;
        }

        const target = document.querySelector(url.hash);

        if (!target) {
            return;
        }

        event.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});
