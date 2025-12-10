/**
 * Web Vitals Collection Module
 *
 * Collects Core Web Vitals metrics and sends them to the backend.
 * Note: FID was removed in web-vitals v4+ in favor of INP.
 */

import { onCLS, onLCP, onINP, onTTFB } from 'web-vitals';

const API_ENDPOINT = '/api/observability/web-vitals';

/**
 * Detect device type based on screen width.
 */
function getDeviceType() {
    const width = window.innerWidth;
    if (width < 768) return 'mobile';
    if (width < 1024) return 'tablet';
    return 'desktop';
}

/**
 * Get the current page path.
 */
function getPagePath() {
    return window.location.pathname;
}

/**
 * Send a web vital metric to the backend.
 */
function sendMetric(metric) {
    const payload = {
        name: metric.name,
        value: metric.value,
        page: getPagePath(),
        device_type: getDeviceType(),
    };

    // Use sendBeacon for reliability during page unload
    if (navigator.sendBeacon) {
        const blob = new Blob([JSON.stringify(payload)], { type: 'application/json' });
        navigator.sendBeacon(API_ENDPOINT, blob);
    } else {
        // Fallback to fetch
        fetch(API_ENDPOINT, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
            keepalive: true,
        }).catch(() => {
            // Silently fail to avoid impacting user experience
        });
    }
}

/**
 * Initialize Web Vitals collection.
 */
export function initWebVitals() {
    // Largest Contentful Paint
    onLCP(sendMetric);

    // Cumulative Layout Shift
    onCLS(sendMetric);

    // Interaction to Next Paint (replaces FID in web-vitals v4+)
    onINP(sendMetric);

    // Time to First Byte
    onTTFB(sendMetric);
}

export default { initWebVitals };
