(function () {
    function escapeSelector(value) {
        if (window.CSS?.escape) {
            return window.CSS.escape(String(value));
        }

        return String(value).replace(/["\\]/g, '\\$&');
    }

    function wait(duration) {
        return new Promise((resolve) => {
            window.setTimeout(resolve, Math.max(0, duration));
        });
    }

    function nextFrame() {
        return new Promise((resolve) => {
            window.requestAnimationFrame(() => resolve());
        });
    }

    function prefersReducedMotion() {
        return window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    }

    class NotificationListAnimator {
        constructor(root, options = {}) {
            this.root = root || null;
            this.duration = Number(options.duration) || 420;
            this.staggerStep = Number(options.staggerStep) || 48;
            this.pendingAnimations = new Map();
        }

        getItem(notificationId) {
            if (!this.root) {
                return null;
            }

            return this.root.querySelector(`.notification-item[data-id="${escapeSelector(notificationId)}"]`);
        }

        isAnimating(notificationId) {
            return this.pendingAnimations.has(String(notificationId));
        }

        hasActiveAnimations() {
            return this.pendingAnimations.size > 0;
        }

        async animateRemoval(notificationId, options = {}) {
            const resolvedId = String(notificationId);
            const element = this.getItem(resolvedId);

            if (!element) {
                return false;
            }

            if (this.pendingAnimations.has(resolvedId)) {
                return this.pendingAnimations.get(resolvedId);
            }

            const animationPromise = this.runRemoval(element, options)
                .finally(() => {
                    this.pendingAnimations.delete(resolvedId);
                });

            this.pendingAnimations.set(resolvedId, animationPromise);
            return animationPromise;
        }

        async animateRemovals(notificationIds, options = {}) {
            const ids = Array.isArray(notificationIds) ? notificationIds.map((id) => String(id)) : [];
            const staggerStep = prefersReducedMotion() ? 0 : (Number(options.staggerStep) || this.staggerStep);

            await Promise.all(ids.map((notificationId, index) => this.animateRemoval(notificationId, {
                ...options,
                stagger: index * staggerStep,
            })));
        }

        async runRemoval(element, options = {}) {
            const duration = prefersReducedMotion() ? 1 : Math.max(180, Number(options.duration) || this.duration);
            const stagger = prefersReducedMotion() ? 0 : Math.max(0, Number(options.stagger) || 0);
            const computedStyle = window.getComputedStyle(element);
            const measuredHeight = element.offsetHeight;
            const shiftDistance = Math.min(56, Math.max(36, Math.round(measuredHeight * 0.28)));

            if (stagger > 0) {
                await wait(stagger);
            }

            if (!element.isConnected) {
                return false;
            }

            element.classList.add('notification-item-animating');
            element.setAttribute('aria-disabled', 'true');
            element.setAttribute('aria-busy', 'true');
            element.style.setProperty('--notification-item-exit-duration', `${duration}ms`);
            element.style.setProperty('--notification-item-exit-shift', `${shiftDistance}px`);
            element.style.height = `${measuredHeight}px`;
            element.style.marginTop = computedStyle.marginTop;
            element.style.marginBottom = computedStyle.marginBottom;
            element.style.paddingTop = computedStyle.paddingTop;
            element.style.paddingBottom = computedStyle.paddingBottom;
            element.style.borderTopWidth = computedStyle.borderTopWidth;
            element.style.borderBottomWidth = computedStyle.borderBottomWidth;

            const interactiveElements = element.querySelectorAll('button, a, input, select, textarea');
            interactiveElements.forEach((interactiveElement) => {
                if ('disabled' in interactiveElement) {
                    interactiveElement.disabled = true;
                }
            });

            await nextFrame();

            element.classList.add('notification-item-removing');
            element.style.height = '0px';
            element.style.marginTop = '0px';
            element.style.marginBottom = '0px';
            element.style.paddingTop = '0px';
            element.style.paddingBottom = '0px';
            element.style.borderTopWidth = '0px';
            element.style.borderBottomWidth = '0px';

            await wait(duration);

            if (element.isConnected) {
                element.remove();
            }

            return true;
        }
    }

    window.NotificationListAnimator = NotificationListAnimator;
})();
