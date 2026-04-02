import './bootstrap';
import 'cropperjs/dist/cropper.css';

import Alpine from 'alpinejs';
import Cropper from 'cropperjs/dist/cropper.js';
import { PDFDocument, StandardFonts, rgb } from 'pdf-lib';

window.Alpine = Alpine;
window.Cropper = Cropper;
window.PDFLib = { PDFDocument, StandardFonts, rgb };

Alpine.start();

const LIBRARY_BRANDING_STORAGE_KEY = 'lms.library-branding';

let currentLibraryBranding = null;

function resolveLibraryLogoUrl(imageUrl) {
    const normalizedImageUrl = String(imageUrl ?? '').trim();

    if (!normalizedImageUrl) {
        return '';
    }

    if (/^(?:data:|blob:)/i.test(normalizedImageUrl)) {
        return normalizedImageUrl;
    }

    if (/^https?:\/\//i.test(normalizedImageUrl)) {
        try {
            const url = new URL(normalizedImageUrl, window.location.origin);
            const currentHost = window.location.hostname;
            const localHosts = new Set(['localhost', '127.0.0.1', '::1', '[::1]']);

            if (localHosts.has(url.hostname) && localHosts.has(currentHost)) {
                return `${window.location.origin}${url.pathname}${url.search}`;
            }

            return url.toString();
        } catch (error) {
            return normalizedImageUrl;
        }
    }

    if (normalizedImageUrl.startsWith('/')) {
        return normalizedImageUrl;
    }

    return `/${normalizedImageUrl.replace(/^\/+/, '')}`;
}

function normalizeLibraryBranding(branding) {
    if (!branding || typeof branding !== 'object') {
        return null;
    }

    const name = String(branding.name ?? 'Library Management System').trim() || 'Library Management System';
    const fallbackText = String(branding.fallback_text ?? 'LMS').trim() || 'LMS';
    const imageUrl = resolveLibraryLogoUrl(branding.image_url);
    const alt = String(branding.alt ?? 'Library Logo').trim() || 'Library Logo';

    return {
        name,
        fallback_text: fallbackText,
        image_url: imageUrl || null,
        alt,
        has_custom_logo: Boolean(imageUrl),
        version: branding.version ?? null,
        updated_at: branding.updated_at ?? null,
    };
}

function renderLibraryLogo(root, branding) {
    if (!(root instanceof HTMLElement)) {
        return;
    }

    const normalizedBranding = normalizeLibraryBranding(branding)
        ?? normalizeLibraryBranding({
            name: root.dataset.libraryLogoName,
            fallback_text: root.dataset.libraryLogoFallbackText,
            image_url: root.dataset.libraryLogoImageUrl,
            alt: root.dataset.libraryLogoAlt,
        });

    if (!normalizedBranding) {
        return;
    }

    const image = root.querySelector('[data-library-logo-image]');
    const fallback = root.querySelector('[data-library-logo-fallback]');
    const badge = root.querySelector('[data-library-logo-badge]');

    root.dataset.libraryLogoName = normalizedBranding.name;
    root.dataset.libraryLogoFallbackText = normalizedBranding.fallback_text;
    root.dataset.libraryLogoImageUrl = normalizedBranding.image_url ?? '';
    root.dataset.libraryLogoAlt = normalizedBranding.alt;

    if (image instanceof HTMLImageElement) {
        image.alt = normalizedBranding.alt;

        if (normalizedBranding.image_url) {
            const expectedSource = normalizedBranding.image_url;

            image.dataset.libraryLogoResolvedSrc = expectedSource;
            image.onerror = () => {
                if (image.dataset.libraryLogoResolvedSrc !== expectedSource) {
                    return;
                }

                image.style.display = 'none';
                image.removeAttribute('src');

                if (fallback instanceof HTMLElement) {
                    fallback.style.display = '';
                }

                if (badge instanceof HTMLElement) {
                    badge.classList.remove('has-image');
                }
            };
            image.src = normalizedBranding.image_url;
            image.style.display = '';
            
            if (badge instanceof HTMLElement) {
                badge.classList.add('has-image');
            }
        } else {
            delete image.dataset.libraryLogoResolvedSrc;
            image.onerror = null;
            image.style.display = 'none';
            image.removeAttribute('src');
            
            if (badge instanceof HTMLElement) {
                badge.classList.remove('has-image');
            }
        }
    }

    if (fallback instanceof HTMLElement) {
        fallback.textContent = normalizedBranding.fallback_text;
        fallback.style.display = normalizedBranding.image_url ? 'none' : '';
    }
}


function applyLibraryBranding(branding, options = {}) {
    const normalizedBranding = normalizeLibraryBranding(branding);
    if (!normalizedBranding) {
        return null;
    }

    currentLibraryBranding = normalizedBranding;
    window.__LIBRARY_BRANDING__ = normalizedBranding;

    document.querySelectorAll('[data-library-logo-root]').forEach((root) => {
        renderLibraryLogo(root, normalizedBranding);
    });

    window.dispatchEvent(new CustomEvent('library-branding:updated', {
        detail: normalizedBranding,
    }));

    if (options.broadcast === true) {
        try {
            window.localStorage.setItem(LIBRARY_BRANDING_STORAGE_KEY, JSON.stringify({
                ...normalizedBranding,
                _timestamp: Date.now(),
            }));
        } catch (error) {
            console.warn('[LibraryBranding] Failed to persist branding payload:', error);
        }
    }

    return normalizedBranding;
}

function getStoredLibraryBranding() {
    try {
        const payload = window.localStorage.getItem(LIBRARY_BRANDING_STORAGE_KEY);
        return payload ? JSON.parse(payload) : null;
    } catch (error) {
        console.warn('[LibraryBranding] Failed to parse stored branding payload:', error);
        return null;
    }
}

window.LibraryBranding = {
    apply(branding) {
        return applyLibraryBranding(branding);
    },
    publish(branding) {
        return applyLibraryBranding(branding, { broadcast: true });
    },
    renderInto(root, branding) {
        renderLibraryLogo(root, branding);
    },
    normalize(branding) {
        return normalizeLibraryBranding(branding);
    },
    getCurrent() {
        return currentLibraryBranding;
    },
};

document.addEventListener('DOMContentLoaded', () => {
    const initialBranding = normalizeLibraryBranding(window.__LIBRARY_BRANDING__) ?? getStoredLibraryBranding();

    if (initialBranding) {
        applyLibraryBranding(initialBranding);
    } else {
        document.querySelectorAll('[data-library-logo-root]').forEach((root) => {
            renderLibraryLogo(root);
        });
    }
});

window.addEventListener('storage', (event) => {
    if (event.key !== LIBRARY_BRANDING_STORAGE_KEY || !event.newValue) {
        return;
    }

    try {
        const branding = JSON.parse(event.newValue);
        applyLibraryBranding(branding);
    } catch (error) {
        console.warn('[LibraryBranding] Failed to process cross-tab branding update:', error);
    }
});
