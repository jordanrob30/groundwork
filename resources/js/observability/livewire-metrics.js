/**
 * Livewire Component Performance Tracking Module
 *
 * Tracks Livewire component render and update times.
 */

const API_ENDPOINT = '/api/observability/livewire-metrics';

/**
 * Send Livewire metrics to the backend.
 */
function sendMetric(component, action, duration) {
    const payload = {
        component,
        action,
        duration,
        page: window.location.pathname,
    };

    fetch(API_ENDPOINT, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
    }).catch(() => {
        // Silently fail to avoid impacting user experience
    });
}

/**
 * Initialize Livewire performance tracking.
 */
export function initLivewireMetrics() {
    // Check if Livewire is available
    if (typeof window.Livewire === 'undefined') {
        return;
    }

    // Track component initialization/render
    document.addEventListener('livewire:init', () => {
        if (window.Livewire && window.Livewire.hook) {
            // Track component updates
            window.Livewire.hook('request', ({ component, succeed, fail }) => {
                const startTime = performance.now();

                succeed(({ snapshot, effects }) => {
                    const duration = (performance.now() - startTime) / 1000; // Convert to seconds
                    const componentName = component?.name || 'unknown';
                    sendMetric(componentName, 'update', duration);
                });

                fail(({ component, message }) => {
                    const componentName = component?.name || 'unknown';
                    // Report the error
                    fetch('/api/observability/js-error', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: JSON.stringify({
                            message: `Livewire error: ${message}`,
                            error_type: 'LivewireError',
                            page: window.location.pathname,
                        }),
                    }).catch(() => {});
                });
            });
        }
    });

    // Track initial component renders using MutationObserver
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === Node.ELEMENT_NODE && node.hasAttribute('wire:id')) {
                    const componentName = node.getAttribute('wire:initial-data')
                        ? JSON.parse(node.getAttribute('wire:initial-data'))?.fingerprint?.name
                        : 'unknown';

                    // We can't measure actual render time from JS, so we just track the event
                    // Real render time would need to be measured server-side
                }
            });
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });
}

export default { initLivewireMetrics };
