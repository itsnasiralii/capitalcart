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
        if (!dot || !dot.animate || !CSS.supports('offset-path', 'path("M 6 6 H 66 Q 78 6 78 18 V 78")')) return;

        const animation = dot.animate(
            [{ offsetDistance: '0%' }, { offsetDistance: '100%' }],
            { duration: 2000, iterations: Infinity, direction: 'alternate', easing: 'linear' }
        );
        animation.currentTime = index * 350;
        animation.updatePlaybackRate(NORMAL_SPEED);
        let hovered = false;

        function sync() {
            if (reducedMotion.matches || document.hidden) {
                animation.pause();
                return;
            }
            animation.updatePlaybackRate(NORMAL_SPEED * (hovered && canHover.matches ? HOVER_MULTIPLIER : 1));
            animation.play();
        }

        card.addEventListener('pointerenter', event => {
            hovered = event.pointerType !== 'touch';
            sync();
        });
        card.addEventListener('pointerleave', () => { hovered = false; sync(); });
        card.addEventListener('pointercancel', () => { hovered = false; sync(); });
        animations.push(sync);
        sync();
    });

    const syncAll = () => animations.forEach(sync => sync());
    reducedMotion.addEventListener('change', syncAll);
    canHover.addEventListener('change', syncAll);
    document.addEventListener('visibilitychange', syncAll);
})();
