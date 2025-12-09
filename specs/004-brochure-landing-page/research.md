# Research: Brochure Landing Page

**Feature**: 004-brochure-landing-page
**Date**: 2025-12-09

## Table of Contents

1. [p5.js Integration with Laravel/Vite](#p5js-integration-with-laravelvite)
2. [Reduced Motion Handling](#reduced-motion-handling)
3. [SaaS Pricing Tier Structure](#saas-pricing-tier-structure)
4. [Landing Page Animation Patterns](#landing-page-animation-patterns)

---

## p5.js Integration with Laravel/Vite

### Decision
Use p5.js in **Instance Mode** with npm installation and Vite bundling.

### Rationale
- Instance mode allows multiple sketches on single page without namespace conflicts
- npm + Vite integration is cleaner than CDN for production builds
- Parent sketches to specific div containers for precise positioning

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| Global mode p5.js | Cannot have multiple sketches, pollutes global namespace |
| CDN-loaded p5.js | Not bundled with app, separate HTTP request, harder to tree-shake |
| CSS-only animations | Cannot achieve the creative, generative effects user requested |

### Implementation Pattern

**Installation:**
```bash
npm install p5
```

**Instance Mode Usage:**
```javascript
import p5 from 'p5';

const heroSketch = (p) => {
  p.setup = () => {
    const container = document.getElementById('hero-canvas');
    p.createCanvas(container.offsetWidth, container.offsetHeight);
  };

  p.draw = () => {
    p.background(15, 15, 20, 10); // Dark background with trail
    // Animation logic
  };

  p.windowResized = () => {
    const container = document.getElementById('hero-canvas');
    p.resizeCanvas(container.offsetWidth, container.offsetHeight);
  };
};

// Initialize when DOM ready
document.addEventListener('DOMContentLoaded', () => {
  new p5(heroSketch, 'hero-canvas');
});
```

**Canvas Positioning (Blade):**
```html
<div id="hero-canvas" class="absolute inset-0 z-0"></div>
```

### Key Recommendations
1. Use `windowResized()` for responsive canvas sizing
2. Use `pixelDensity(displayDensity())` for retina displays
3. Use `frameRate(30)` for decorative animations (save CPU)
4. Position canvas with CSS `z-index: -1` or `z-index: 0` for backgrounds

---

## Reduced Motion Handling

### Decision
Detect `prefers-reduced-motion` and simplify (not disable) animations when enabled.

### Rationale
- Complete disablement harms UX for all users
- Simplification (static or fading elements) still provides visual interest
- Small movements (<5px) and opacity changes are generally safe

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| Disable all animations | Removes visual interest, harms UX |
| Ignore preference | Accessibility violation, can cause vestibular issues |
| No-motion-first | More complex, user expects visual engagement by default |

### Implementation Pattern

```javascript
let prefersReducedMotion = false;

const sketch = (p) => {
  p.setup = () => {
    // Detect initial preference
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: no-preference)');
    prefersReducedMotion = !mediaQuery.matches;

    // Listen for changes
    mediaQuery.addEventListener('change', (e) => {
      prefersReducedMotion = !e.matches;
    });

    p.createCanvas(400, 400);
  };

  p.draw = () => {
    if (prefersReducedMotion) {
      // Simplified: static or subtle fade
      p.background(15, 15, 20);
      p.fill(20, 184, 166, 50); // Static teal glow
      p.ellipse(p.width / 2, p.height / 2, 100);
    } else {
      // Full animation with movement
      p.background(15, 15, 20, 20);
      const y = p.height / 2 + p.sin(p.frameCount * 0.05) * 30;
      p.fill(20, 184, 166);
      p.ellipse(p.width / 2, y, 100);
    }
  };
};
```

### Safe vs. Unsafe Animations

**Safe (keep with reduced motion):**
- Opacity/fade transitions
- Subtle color changes
- Static decorative elements

**Unsafe (disable with reduced motion):**
- Continuous motion (orbiting, bouncing)
- Parallax effects
- Scroll-triggered movement
- Scale/zoom animations

---

## SaaS Pricing Tier Structure

### Decision
Implement 3-tier flat-rate pricing: Starter ($49), Professional ($99), Enterprise ($249).

### Rationale
- 3 tiers avoid analysis paralysis while providing clear upgrade paths
- Flat-rate pricing appeals to agencies and teams (not per-seat)
- AI response analysis (Claude-powered) is key differentiator
- Price points competitive with industry ($30-250 range)

### Alternatives Considered
| Alternative | Rejected Because |
|-------------|------------------|
| 4+ tiers | Too complex, causes decision fatigue |
| Per-seat pricing | Penalizes team growth, agencies dislike |
| Usage-only pricing | Hard to predict costs, customer friction |
| Free tier | Devalues AI features, attracts non-converting users |

### Recommended Pricing Structure

#### Starter - $49/month ($39/month annual)
**Target**: Individual outreach, testing the platform

| Feature | Limit |
|---------|-------|
| Emails/month | 5,000 |
| Email accounts | 3 |
| Active leads | 2,000 |
| AI response analysis | 100/month |
| Email warmup | Included |
| Support | Email/chat |

#### Professional - $99/month ($79/month annual) **[RECOMMENDED]**
**Target**: Growing teams, serious outreach

| Feature | Limit |
|---------|-------|
| Emails/month | 50,000 |
| Email accounts | 10 |
| Active leads | 10,000 |
| AI response analysis | Unlimited |
| Inbox rotation | Included |
| A/B testing | Included |
| Analytics | Advanced |
| API access | Included |
| Support | Priority |

#### Enterprise - $249/month ($199/month annual)
**Target**: Agencies, high-volume teams

| Feature | Limit |
|---------|-------|
| Emails/month | Unlimited |
| Email accounts | Unlimited |
| Active leads | 100,000+ |
| AI response analysis | Unlimited + Custom training |
| Multi-client management | Included |
| White-labeling | Included |
| Integrations | Salesforce, HubSpot |
| Support | Dedicated manager |

### Display Recommendations
1. Highlight "Professional" tier as recommended (badge/border)
2. Show annual savings toggle (20% discount)
3. Include feature comparison table below cards
4. Use contrasting CTA buttons ("Start Free Trial")
5. Add trust signals near pricing (testimonials, logos)

---

## Landing Page Animation Patterns

### Decision
Create 2-3 p5.js sketches: hero background (particle/network effect), pricing section accent.

### Rationale
- Multiple subtle sketches > one overwhelming animation
- Hero background creates immediate visual impact
- Section accents reinforce brand identity without distraction
- Particle/network effects align with "AI/tech" positioning

### Animation Concepts

#### 1. Hero Background - Particle Network
Connected nodes that drift and form connections, representing AI network intelligence.
- Teal particles (#14b8a6) on dark background (#0f0f14)
- Subtle amber (#f59e0b) accent particles
- Mouse interaction: nearby particles attract/glow brighter
- Reduced motion: static network, gentle pulse

#### 2. Pricing Section - Floating Elements
Subtle geometric shapes floating behind pricing cards.
- Very low opacity, background decoration
- Shapes rotate slowly
- Adds depth without distraction
- Reduced motion: static gradient or removed

#### 3. Feature Section - Data Flow (Optional)
Animated lines/dots representing email flow and AI processing.
- Triggered on scroll into view
- Brief animation, then settles
- Reduced motion: static representation

### Performance Guidelines
- Use `frameRate(30)` for background sketches
- Use `noLoop()` + timed `redraw()` for subtle animations
- Lazy-initialize sketches below fold with IntersectionObserver
- Total combined animations should not exceed 20% CPU on mobile

---

## Summary

All research topics resolved. Key decisions:

1. **p5.js**: Install via npm, use instance mode, parent to container divs
2. **Reduced motion**: Detect preference, simplify (not disable) animations
3. **Pricing**: 3 tiers ($49/$99/$249), flat-rate, highlight Professional
4. **Animations**: Particle network hero, subtle accents, respect accessibility
