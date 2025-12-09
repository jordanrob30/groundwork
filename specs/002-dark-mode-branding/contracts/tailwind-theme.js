/**
 * Tailwind Theme Extension for Dark Mode Branding
 *
 * Feature: 002-dark-mode-branding
 * Generated: 2025-12-09
 *
 * This module exports the theme extension to be merged into tailwind.config.js
 *
 * Usage in tailwind.config.js:
 * ```javascript
 * import darkModeTheme from './specs/002-dark-mode-branding/contracts/tailwind-theme.js';
 *
 * export default {
 *   theme: {
 *     extend: {
 *       ...darkModeTheme.extend,
 *     },
 *   },
 * };
 * ```
 */

const darkModeTheme = {
  extend: {
    colors: {
      // Background colors
      'bg-base': 'var(--color-bg-base)',
      'bg-elevated': 'var(--color-bg-elevated)',
      'bg-surface': 'var(--color-bg-surface)',
      'bg-overlay': 'var(--color-bg-overlay)',

      // Text colors
      'text-primary': 'var(--color-text-primary)',
      'text-secondary': 'var(--color-text-secondary)',
      'text-muted': 'var(--color-text-muted)',
      'text-inverse': 'var(--color-text-inverse)',

      // Border colors
      'border-default': 'var(--color-border-default)',
      'border-muted': 'var(--color-border-muted)',
      'border-focus': 'var(--color-border-focus)',

      // Brand colors
      'brand': {
        DEFAULT: 'var(--color-brand)',
        hover: 'var(--color-brand-hover)',
        active: 'var(--color-brand-active)',
      },

      // Accent colors
      'accent': {
        DEFAULT: 'var(--color-accent)',
        hover: 'var(--color-accent-hover)',
        active: 'var(--color-accent-active)',
      },

      // Semantic state colors
      'success': {
        DEFAULT: 'var(--color-success)',
        bg: 'var(--color-success-bg)',
      },
      'warning': {
        DEFAULT: 'var(--color-warning)',
        bg: 'var(--color-warning-bg)',
      },
      'error': {
        DEFAULT: 'var(--color-error)',
        bg: 'var(--color-error-bg)',
      },
      'info': {
        DEFAULT: 'var(--color-info)',
        bg: 'var(--color-info-bg)',
      },

      // Lead interest level colors
      'lead-hot': {
        bg: 'var(--color-lead-hot-bg)',
        text: 'var(--color-lead-hot-text)',
        border: 'var(--color-lead-hot-border)',
      },
      'lead-warm': {
        bg: 'var(--color-lead-warm-bg)',
        text: 'var(--color-lead-warm-text)',
        border: 'var(--color-lead-warm-border)',
      },
      'lead-cold': {
        bg: 'var(--color-lead-cold-bg)',
        text: 'var(--color-lead-cold-text)',
        border: 'var(--color-lead-cold-border)',
      },
      'lead-negative': {
        bg: 'var(--color-lead-negative-bg)',
        text: 'var(--color-lead-negative-text)',
        border: 'var(--color-lead-negative-border)',
      },
    },

    // Ring colors for focus states
    ringColor: {
      brand: 'var(--color-brand)',
      error: 'var(--color-error)',
    },

    // Ring offset colors
    ringOffsetColor: {
      base: 'var(--color-bg-base)',
      elevated: 'var(--color-bg-elevated)',
    },

    // Background opacity utilities
    backgroundColor: {
      'overlay': 'rgba(15, 15, 20, 0.8)',
    },
  },
};

export default darkModeTheme;
