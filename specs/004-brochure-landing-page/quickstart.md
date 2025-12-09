# Quickstart: Brochure Landing Page

**Feature**: 004-brochure-landing-page
**Date**: 2025-12-09

## Prerequisites

- Laravel Sail environment running (`./vendor/bin/sail up`)
- Node.js and npm available in Sail container
- Existing Groundwork codebase with dark mode styling

## Setup Steps

### 1. Install p5.js Dependency

```bash
./vendor/bin/sail npm install p5
```

Verify installation:
```bash
./vendor/bin/sail npm list p5
```

### 2. Create Animation Module

Create the p5.js sketch file:

```bash
touch resources/js/landing-animations.js
```

Basic structure:
```javascript
// resources/js/landing-animations.js
import p5 from 'p5';

let prefersReducedMotion = false;

// Detect reduced motion preference
function initReducedMotionDetection() {
  const mediaQuery = window.matchMedia('(prefers-reduced-motion: no-preference)');
  prefersReducedMotion = !mediaQuery.matches;
  mediaQuery.addEventListener('change', (e) => {
    prefersReducedMotion = !e.matches;
  });
}

// Hero background sketch
const heroSketch = (p) => {
  p.setup = () => {
    const container = document.getElementById('hero-canvas');
    if (!container) return;
    p.createCanvas(container.offsetWidth, container.offsetHeight);
    p.frameRate(30);
  };

  p.draw = () => {
    if (prefersReducedMotion) {
      // Simplified static version
      p.background(15, 15, 20);
    } else {
      // Full animation
      p.background(15, 15, 20, 20);
      // ... animation logic
    }
  };

  p.windowResized = () => {
    const container = document.getElementById('hero-canvas');
    if (!container) return;
    p.resizeCanvas(container.offsetWidth, container.offsetHeight);
  };
};

// Initialize on DOM ready
export function initAnimations() {
  initReducedMotionDetection();

  if (document.getElementById('hero-canvas')) {
    new p5(heroSketch, 'hero-canvas');
  }
}
```

### 3. Import in Main App Entry

Update `resources/js/app.js`:

```javascript
import './bootstrap';
import { initAnimations } from './landing-animations';

// Initialize landing page animations if on welcome page
if (document.getElementById('hero-canvas')) {
  initAnimations();
}
```

### 4. Update Welcome Template

Modify `resources/views/welcome.blade.php` to include canvas containers:

```blade
<body class="antialiased font-sans bg-bg-base text-text-primary">
    {{-- Hero Section with Canvas --}}
    <section id="hero" class="relative min-h-screen">
        <div id="hero-canvas" class="absolute inset-0 z-0"></div>
        <div class="relative z-10">
            {{-- Hero content --}}
        </div>
    </section>

    {{-- ... other sections --}}
</body>
```

### 5. Rebuild Assets

```bash
./vendor/bin/sail npm run build
```

For development with hot reload:
```bash
./vendor/bin/sail npm run dev
```

## Testing the Implementation

### Visual Testing

1. Start the development server:
   ```bash
   ./vendor/bin/sail composer dev
   ```

2. Open `http://localhost` in browser

3. Verify:
   - [ ] Hero animation renders and moves smoothly
   - [ ] Scrolling to sections works with smooth scroll
   - [ ] Pricing section displays 3 tiers
   - [ ] CTAs link to `/register`

### Reduced Motion Testing

1. Enable reduced motion in OS:
   - **macOS**: System Preferences > Accessibility > Display > Reduce Motion
   - **Windows**: Settings > Ease of Access > Display > Show animations

2. Reload the page and verify:
   - [ ] Animations are simplified or static
   - [ ] Page remains visually appealing
   - [ ] All content is still accessible

### Responsive Testing

Test at these breakpoints:
- Mobile: 375px (iPhone SE)
- Tablet: 768px (iPad)
- Desktop: 1280px
- Large: 1920px

Verify:
- [ ] No horizontal scrolling
- [ ] Pricing cards stack on mobile
- [ ] Text remains readable
- [ ] Canvas resizes appropriately

### Performance Testing

1. Open Chrome DevTools > Performance

2. Record page load and scroll

3. Verify:
   - [ ] Animation frame rate ~30fps (target 60fps max)
   - [ ] No jank during scroll
   - [ ] CPU usage reasonable (<20% during animations)

## Troubleshooting

### Canvas Not Rendering

1. Check container exists in DOM:
   ```javascript
   console.log(document.getElementById('hero-canvas'));
   ```

2. Verify Vite is bundling p5.js:
   ```bash
   ./vendor/bin/sail npm run build -- --debug
   ```

3. Check browser console for errors

### Animation Performance Issues

1. Reduce frameRate:
   ```javascript
   p.frameRate(24); // Lower from 30
   ```

2. Simplify particle count or effects

3. Use `noLoop()` for static-ish animations:
   ```javascript
   p.setup = () => {
     // ...
     p.noLoop();
     setInterval(() => p.redraw(), 100); // Redraw every 100ms
   };
   ```

### Styling Conflicts

1. Ensure canvas container has `position: relative` or `absolute`
2. Verify z-index layering (canvas at -1 or 0, content at 10+)
3. Check that Tailwind classes aren't overriding canvas dimensions

## File Summary

After implementation, these files should be modified/created:

| File | Status | Description |
|------|--------|-------------|
| `package.json` | Modified | Added p5 dependency |
| `resources/js/landing-animations.js` | New | p5.js sketch definitions |
| `resources/js/app.js` | Modified | Import and init animations |
| `resources/views/welcome.blade.php` | Modified | Full landing page redesign |
| `resources/css/app.css` | Modified | Any additional landing styles |
