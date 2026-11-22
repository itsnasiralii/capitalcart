(() => {
    // EDIT THESE TWO VALUES TO CHANGE THE SPEED:
    const NORMAL_SPEED = 0.5;       // 0.25 = slower; 1 = twice the current speed.
    const HOVER_MULTIPLIER = 5;     // Hover speed = NORMAL_SPEED * this value (2.5 by default).

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const canHover = window.matchMedia('(hover: hover)');
    const cards = document.querySelectorAll('.home-storefront #featured-products .featured-corner-card');
    const animations = [];

    cards.forEach((card, index) => {
        const dot = card.querySelector('.featured-corner-dot');
        if (!dot || !dot.animate || !CSS.supports('offset-path', 'path("M 0 0 H 10 V 10 Z")')) return;

        // Follow the entire card, including after mobile/layout resizing.
        function fitPath() {
            const right = card.clientWidth - 6;
            const bottom = card.clientHeight - 6;
            dot.style.offsetPath = `path("M 14 6 H ${right - 8} Q ${right} 6 ${right} 14 V ${bottom - 8} Q ${right} ${bottom} ${right - 8} ${bottom} H 14 Q 6 ${bottom} 6 ${bottom - 8} V 14 Q 6 6 14 6 Z")`;
        }
        fitPath();
        new ResizeObserver(fitPath).observe(card);

        const animation = dot.animate(
            [{ offsetDistance: '0%' }, { offsetDistance: '100%' }],
            { duration: 6000, iterations: Infinity, easing: 'linear' }
        );
        animation.currentTime = index * 350;
        animation.updatePlaybackRate(NORMAL_SPEED);
        let hovered = false;
        let clicked = false;
        let clickTimer;
        let dotPulse;
        let buttonPulse;

        function sync() {
            if (reducedMotion.matches || document.hidden) {
                animation.pause();
                dotPulse?.cancel();
                buttonPulse?.cancel();
                return;
            }
            animation.updatePlaybackRate(NORMAL_SPEED * (clicked || (hovered && canHover.matches) ? HOVER_MULTIPLIER : 1));
            animation.play();
        }

        card.addEventListener('pointerenter', event => {
            hovered = event.pointerType !== 'touch';
            sync();
        });
        card.addEventListener('pointerleave', () => { hovered = false; sync(); });
        card.addEventListener('pointercancel', () => { hovered = false; sync(); });
        card.addEventListener('click', () => {
            if (reducedMotion.matches) return;
            clicked = true;
            clearTimeout(clickTimer);
            clickTimer = setTimeout(() => { clicked = false; sync(); }, 900);
            sync();
            // A brief soft pulse; never delay navigation or use rapid flashing.
            dotPulse?.cancel();
            dotPulse = dot.animate([
                { boxShadow: '0 0 0 0 rgba(250,204,21,0)' },
                { boxShadow: '0 0 10px 4px rgba(250,204,21,.65)' },
                { boxShadow: '0 0 0 0 rgba(250,204,21,0)' }
            ], { duration: 650, easing: 'ease-out' });
            const button = card.querySelector('.btn-add-cart');
            if (button) {
                buttonPulse?.cancel();
                buttonPulse = button.animate([
                    { transform: 'scale(1)' },
                    { transform: 'scale(.97)', boxShadow: '0 0 0 3px rgba(7,84,215,.2)' },
                    { transform: 'scale(1)' }
                ], { duration: 350, easing: 'ease-out' });
            }
        });
        animations.push(sync);
        sync();
    });

    const syncAll = () => animations.forEach(sync => sync());
    reducedMotion.addEventListener('change', syncAll);
    canHover.addEventListener('change', syncAll);
    document.addEventListener('visibilitychange', syncAll);
})();
