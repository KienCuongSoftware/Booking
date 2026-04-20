import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

document.addEventListener('alpine:init', () => {
    Alpine.data('customerInboxBadge', () => ({
        unread: 0,
        init() {
            this.pull();
            setInterval(() => this.pull(), 45_000);
        },
        async pull() {
            try {
                const res = await fetch('/customer/inbox/unread-count', {
                    credentials: 'same-origin',
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (! res.ok) {
                    return;
                }
                const data = await res.json();
                this.unread = Number(data.unread) || 0;
            } catch {
                /* ignore */
            }
        },
    }));
});

function initAjaxFilterForms(root = document) {
    const forms = Array.from(root.querySelectorAll('form[data-ajax-filter-form]'));
    if (forms.length === 0) {
        return;
    }

    const bindForm = (form) => {
        if (form.dataset.ajaxBound === '1') {
            return;
        }

        form.dataset.ajaxBound = '1';
        const targetSelector = form.dataset.ajaxTarget;
        if (!targetSelector) {
            return;
        }

        let debounceTimer = null;
        let controller = null;
        const debounceMs = Number(form.dataset.ajaxDebounce || 350);

        const setLoading = (loading) => {
            const target = document.querySelector(targetSelector);
            if (!target) {
                return;
            }
            target.classList.toggle('pointer-events-none', loading);
            target.classList.toggle('opacity-70', loading);
        };

        const syncForms = () => {
            const syncTargets = (form.dataset.ajaxSyncWith || '')
                .split(',')
                .map((selector) => selector.trim())
                .filter(Boolean);

            if (syncTargets.length === 0) {
                return;
            }

            const sourceData = new FormData(form);
            syncTargets.forEach((selector) => {
                const linkedForm = document.querySelector(selector);
                if (!linkedForm || linkedForm === form) {
                    return;
                }

                Array.from(linkedForm.elements).forEach((element) => {
                    if (!element.name) {
                        return;
                    }

                    const values = sourceData.getAll(element.name);
                    if (element.type === 'checkbox' || element.type === 'radio') {
                        element.checked = values.includes(element.value);
                        return;
                    }
                    element.value = values.length > 0 ? String(values[0] ?? '') : '';
                });
            });
        };

        const requestAndReplace = async (url, options = {}) => {
            const { pushState = true } = options;
            syncForms();

            if (controller) {
                controller.abort();
            }
            controller = new AbortController();
            setLoading(true);

            try {
                const response = await fetch(url, {
                    signal: controller.signal,
                    credentials: 'same-origin',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });

                if (!response.ok) {
                    window.location.href = url;
                    return;
                }

                const html = await response.text();
                const doc = new DOMParser().parseFromString(html, 'text/html');
                const currentTarget = document.querySelector(targetSelector);
                const nextTarget = doc.querySelector(targetSelector);

                if (!currentTarget || !nextTarget) {
                    window.location.href = url;
                    return;
                }

                currentTarget.replaceWith(nextTarget);
                initAjaxFilterForms(document);

                if (pushState) {
                    window.history.pushState({ ajaxFilter: true }, '', url);
                }
            } catch (error) {
                if (error?.name !== 'AbortError') {
                    window.location.href = url;
                }
            } finally {
                setLoading(false);
            }
        };

        const submitCurrentForm = (pushState = true) => {
            const action = form.getAttribute('action') || window.location.pathname;
            const url = new URL(action, window.location.origin);
            const params = new URLSearchParams(new FormData(form));
            url.search = params.toString();
            void requestAndReplace(url.toString(), { pushState });
        };

        const scheduleSubmit = () => {
            window.clearTimeout(debounceTimer);
            debounceTimer = window.setTimeout(() => submitCurrentForm(), debounceMs);
        };

        form.addEventListener('submit', (event) => {
            event.preventDefault();
            submitCurrentForm();
        });

        form.addEventListener('input', (event) => {
            const element = event.target;
            if (!(element instanceof HTMLInputElement || element instanceof HTMLTextAreaElement)) {
                return;
            }

            if (['checkbox', 'radio', 'hidden', 'submit', 'button'].includes(element.type)) {
                return;
            }

            scheduleSubmit();
        });

        form.addEventListener('change', (event) => {
            const element = event.target;
            if (!(element instanceof HTMLInputElement || element instanceof HTMLSelectElement || element instanceof HTMLTextAreaElement)) {
                return;
            }
            submitCurrentForm();
        });

        const bindPaginationClicks = () => {
            const targetContainer = document.querySelector(targetSelector);
            if (!targetContainer || targetContainer.dataset.ajaxPagerBound === '1') {
                return;
            }

            targetContainer.dataset.ajaxPagerBound = '1';
            targetContainer.addEventListener('click', (event) => {
                const link = event.target instanceof Element ? event.target.closest('a[href]') : null;
                if (!link) {
                    return;
                }

                const url = new URL(link.href, window.location.origin);
                if (url.origin !== window.location.origin) {
                    return;
                }
                if (url.pathname !== window.location.pathname) {
                    return;
                }

                event.preventDefault();
                void requestAndReplace(url.toString(), { pushState: true });
            });
        };
        bindPaginationClicks();

        if (!window.__ajaxFilterPopstateBound) {
            window.addEventListener('popstate', () => {
                window.location.reload();
            });
            window.__ajaxFilterPopstateBound = '1';
        }
    };

    forms.forEach(bindForm);
}

document.addEventListener('DOMContentLoaded', () => {
    initAjaxFilterForms();
});

Alpine.start();
