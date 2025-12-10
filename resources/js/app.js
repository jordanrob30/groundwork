import './bootstrap';
import { initAnimations } from './landing-animations';
import { initObservability } from './observability';

// Initialize observability (Web Vitals, error tracking, etc.)
initObservability();

// Initialize landing page animations if any canvas container exists
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('hero-canvas') ||
        document.getElementById('features-canvas') ||
        document.getElementById('pricing-canvas')) {
        initAnimations();
    }
});
