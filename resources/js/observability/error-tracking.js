/**
 * JavaScript Error Tracking Module
 *
 * Captures JavaScript errors and unhandled promise rejections.
 */

const API_ENDPOINT = '/api/observability/js-error';

/**
 * Get the current page path.
 */
function getPagePath() {
    return window.location.pathname;
}

/**
 * Send an error report to the backend.
 */
function sendError(errorData) {
    const payload = {
        message: errorData.message || 'Unknown error',
        error_type: errorData.type || 'Error',
        page: getPagePath(),
        stack: errorData.stack || null,
        user_agent: navigator.userAgent,
    };

    fetch(API_ENDPOINT, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify(payload),
    }).catch(() => {
        // Silently fail to avoid infinite error loops
    });
}

/**
 * Handle global errors.
 */
function handleError(event) {
    const { message, filename, lineno, colno, error } = event;

    sendError({
        message: message || 'Unknown error',
        type: error?.name || 'Error',
        stack: error?.stack || `${filename}:${lineno}:${colno}`,
    });
}

/**
 * Handle unhandled promise rejections.
 */
function handleUnhandledRejection(event) {
    const reason = event.reason;

    sendError({
        message: reason?.message || String(reason) || 'Unhandled Promise Rejection',
        type: 'UnhandledPromiseRejection',
        stack: reason?.stack || null,
    });
}

/**
 * Initialize error tracking.
 */
export function initErrorTracking() {
    window.addEventListener('error', handleError);
    window.addEventListener('unhandledrejection', handleUnhandledRejection);
}

/**
 * Manually report an error.
 */
export function reportError(error, context = {}) {
    sendError({
        message: error.message || String(error),
        type: error.name || 'Error',
        stack: error.stack || null,
        ...context,
    });
}

export default { initErrorTracking, reportError };
