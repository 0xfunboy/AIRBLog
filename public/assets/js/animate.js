document.addEventListener('DOMContentLoaded', () => {
    const animatedElements = document.querySelectorAll('[data-animate]');
    const mobileToggleButton = document.querySelector('[data-toggle-mobile-nav]');
    const mobileNav = document.querySelector('[data-mobile-nav]');
    const dropdowns = document.querySelectorAll('[data-dropdown]');

    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('animate-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.1 });

        animatedElements.forEach(element => {
            const delay = parseInt(element.getAttribute('data-animate-delay') || '0', 10);
            if (delay) {
                element.style.transitionDelay = `${delay}ms`;
            }
            observer.observe(element);
        });
    } else {
        animatedElements.forEach(element => element.classList.add('animate-visible'));
    }

    if (mobileToggleButton && mobileNav) {
        mobileToggleButton.addEventListener('click', () => {
            mobileNav.classList.toggle('hidden');
        });
    }

    dropdowns.forEach(dropdown => {
        const toggle = dropdown.querySelector('[data-dropdown-toggle]');
        const panel = dropdown.querySelector('[data-dropdown-panel]');

        if (!toggle || !panel) {
            return;
        }

        toggle.addEventListener('click', (event) => {
            event.stopPropagation();
            panel.classList.toggle('hidden');
        });

        panel.addEventListener('click', (event) => {
            event.stopPropagation();
        });

        document.addEventListener('click', () => {
            panel.classList.add('hidden');
        });
    });

    document.querySelectorAll('[data-copy-text]').forEach((button) => {
        button.addEventListener('click', async () => {
            const text = button.getAttribute('data-copy-text');
            if (!text) {
                return;
            }

            try {
                if (navigator.clipboard?.writeText) {
                    await navigator.clipboard.writeText(text);
                } else {
                    const textarea = document.createElement('textarea');
                    textarea.value = text;
                    textarea.setAttribute('readonly', '');
                    textarea.style.position = 'absolute';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                }

                button.classList.add('bg-pri', 'text-white');
                const original = button.innerHTML;
                button.innerHTML = '✓';
                setTimeout(() => {
                    button.innerHTML = original;
                    button.classList.remove('bg-pri', 'text-white');
                }, 2000);
            } catch (error) {
                console.error('Copy failed', error);
            }
        });
    });
});
