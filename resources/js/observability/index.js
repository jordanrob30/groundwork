/**
 * Observability Module Index
 *
 * Exports and initializes all observability modules.
 */

import { initWebVitals } from './web-vitals';
import { initErrorTracking, reportError } from './error-tracking';
import { initLivewireMetrics } from './livewire-metrics';

const PAGE_LOAD_ENDPOINT = '/api/observability/page-load';

/**
 * Get device type based on screen width.
 */
function getDeviceType() {
    const width = window.innerWidth;
    if (width < 768) return 'mobile';
    if (width < 1024) return 'tablet';
    return 'desktop';
}

/**
 * Track page load performance.
 */
function trackPageLoad() {
    if (!window.performance || !window.performance.timing) {
        return;
    }

    // Wait for the page to fully load
    window.addEventListener('load', () => {
        // Use requestIdleCallback or setTimeout to not block rendering
        const reportTiming = () => {
            const timing = window.performance.timing;
            const loadDuration = (timing.loadEventEnd - timing.navigationStart) / 1000;
            const domReadyDuration = (timing.domContentLoadedEventEnd - timing.navigationStart) / 1000;

            // Only report if we have valid values
            if (loadDuration > 0 && loadDuration < 60) {
                fetch(PAGE_LOAD_ENDPOINT, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        page: window.location.pathname,
                        load_duration: loadDuration,
                        dom_ready_duration: domReadyDuration > 0 ? domReadyDuration : null,
                        device_type: getDeviceType(),
                    }),
                }).catch(() => {
                    // Silently fail
                });
            }
        };

        if ('requestIdleCallback' in window) {
            window.requestIdleCallback(reportTiming);
        } else {
            setTimeout(reportTiming, 100);
        }
    });
}

/**
 * Initialize all observability modules.
 */
export function initObservability() {
    // Initialize Core Web Vitals tracking
    initWebVitals();

    // Initialize error tracking
    initErrorTracking();

    // Initialize Livewire metrics
    initLivewireMetrics();

    // Track page load performance
    trackPageLoad();
}

// Export individual modules for granular control
export {
    initWebVitals,
    initErrorTracking,
    reportError,
    initLivewireMetrics,
};

export default { initObservability };
