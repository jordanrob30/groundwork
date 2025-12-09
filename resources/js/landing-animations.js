/**
 * Landing Page Animations
 * Feature: 004-brochure-landing-page
 *
 * p5.js-powered animations for hero, features, and pricing sections
 * Respects prefers-reduced-motion accessibility setting
 */

import p5 from 'p5';

// Global state for reduced motion preference
let prefersReducedMotion = false;

// Brand colors
const COLORS = {
    teal: [20, 184, 166],      // #14b8a6
    amber: [245, 158, 11],     // #f59e0b
    bgBase: [15, 15, 20],      // #0f0f14
    bgElevated: [26, 27, 38],  // #1a1b26
};

/**
 * Initialize reduced motion detection
 * Listens for changes to user's OS preference
 */
function initReducedMotionDetection() {
    const mediaQuery = window.matchMedia('(prefers-reduced-motion: no-preference)');
    prefersReducedMotion = !mediaQuery.matches;

    mediaQuery.addEventListener('change', (e) => {
        prefersReducedMotion = !e.matches;
    });
}

/**
 * Hero background sketch - Particle Network
 * Connected nodes that drift and form connections
 */
const heroSketch = (p) => {
    let particles = [];
    const particleCount = 50;
    const connectionDistance = 150;

    p.setup = () => {
        const container = document.getElementById('hero-canvas');
        if (!container) return;

        const canvas = p.createCanvas(container.offsetWidth, container.offsetHeight);
        canvas.parent('hero-canvas');

        p.frameRate(30);
        p.pixelDensity(Math.min(p.displayDensity(), 2));

        for (let i = 0; i < particleCount; i++) {
            particles.push({
                x: p.random(p.width),
                y: p.random(p.height),
                vx: p.random(-0.5, 0.5),
                vy: p.random(-0.5, 0.5),
                size: p.random(3, 6),
                isAccent: p.random() > 0.85
            });
        }
    };

    p.draw = () => {
        if (prefersReducedMotion) {
            p.background(...COLORS.bgBase);
            p.noStroke();

            particles.forEach(particle => {
                const color = particle.isAccent ? COLORS.amber : COLORS.teal;
                p.fill(...color, 100);
                p.ellipse(particle.x, particle.y, particle.size * 2);
            });

            p.stroke(...COLORS.teal, 30);
            p.strokeWeight(1);
            for (let i = 0; i < particles.length; i++) {
                for (let j = i + 1; j < particles.length; j++) {
                    const d = p.dist(particles[i].x, particles[i].y, particles[j].x, particles[j].y);
                    if (d < connectionDistance) {
                        p.line(particles[i].x, particles[i].y, particles[j].x, particles[j].y);
                    }
                }
            }

            p.noLoop();
            return;
        }

        p.background(...COLORS.bgBase, 25);

        particles.forEach(particle => {
            particle.x += particle.vx;
            particle.y += particle.vy;

            if (particle.x < 0) particle.x = p.width;
            if (particle.x > p.width) particle.x = 0;
            if (particle.y < 0) particle.y = p.height;
            if (particle.y > p.height) particle.y = 0;

            const color = particle.isAccent ? COLORS.amber : COLORS.teal;
            p.noStroke();
            p.fill(...color, 180);
            p.ellipse(particle.x, particle.y, particle.size);

            p.fill(...color, 50);
            p.ellipse(particle.x, particle.y, particle.size * 2);
        });

        p.strokeWeight(1);
        for (let i = 0; i < particles.length; i++) {
            for (let j = i + 1; j < particles.length; j++) {
                const d = p.dist(particles[i].x, particles[i].y, particles[j].x, particles[j].y);
                if (d < connectionDistance) {
                    const alpha = p.map(d, 0, connectionDistance, 80, 0);
                    p.stroke(...COLORS.teal, alpha);
                    p.line(particles[i].x, particles[i].y, particles[j].x, particles[j].y);
                }
            }
        }
    };

    p.windowResized = () => {
        const container = document.getElementById('hero-canvas');
        if (!container) return;
        p.resizeCanvas(container.offsetWidth, container.offsetHeight);

        particles.forEach(particle => {
            if (particle.x > p.width) particle.x = p.random(p.width);
            if (particle.y > p.height) particle.y = p.random(p.height);
        });
    };
};

/**
 * Features background sketch - Flying Logos & Envelopes
 * Groundwork logo (foundation blocks pyramid) and envelopes flying like emails being sent
 * Both have comet trails behind them
 */
const featuresSketch = (p) => {
    let flyingObjects = [];
    const objectCount = 12;

    class FlyingObject {
        constructor(type) {
            this.type = type; // 'logo' or 'envelope'
            this.reset();
            // Start at random position for initial spread
            this.x = p.random(p.width);
            this.y = p.random(p.height);
        }

        reset() {
            // Start from left side, random height
            this.x = p.random(-100, -50);
            this.y = p.random(p.height * 0.1, p.height * 0.9);

            // Fly towards right with slight upward/downward angle
            this.speed = p.random(2, 4);
            this.angle = p.random(-0.15, 0.15);
            this.vx = this.speed * p.cos(this.angle);
            this.vy = this.speed * p.sin(this.angle);

            // Wobble parameters
            this.wobbleOffset = p.random(p.TWO_PI);
            this.wobbleSpeed = p.random(0.02, 0.04);
            this.wobbleAmount = p.random(0.3, 0.8);

            // Size and rotation
            this.size = p.random(25, 40);
            this.rotation = 0; // Keep logos upright
            this.rotationSpeed = p.random(-0.01, 0.01);

            // Comet trail - store more positions for longer trail
            this.trail = [];
            this.maxTrailLength = 20;

            // Alpha for fade effect
            this.alpha = p.random(80, 140);
        }

        update() {
            // Add current position to trail
            this.trail.push({ x: this.x, y: this.y, rotation: this.rotation });
            if (this.trail.length > this.maxTrailLength) {
                this.trail.shift();
            }

            // Update position with wobble
            const wobble = p.sin(p.frameCount * this.wobbleSpeed + this.wobbleOffset) * this.wobbleAmount;
            this.x += this.vx;
            this.y += this.vy + wobble;

            // Slight rotation for envelopes only
            if (this.type === 'envelope') {
                this.rotation += this.rotationSpeed;
            }

            // Reset if off screen
            if (this.x > p.width + 100) {
                this.reset();
            }
        }

        draw() {
            const color = this.type === 'logo' ? COLORS.teal : COLORS.amber;

            // Draw comet trail - thick tapering trail that gets thinner toward the tail
            if (this.trail.length > 1) {
                // Draw the tapering comet body using connected segments
                // Each segment is drawn as a quad that tapers from thick (at object) to thin (at tail)
                p.noStroke();

                for (let i = 0; i < this.trail.length - 1; i++) {
                    const t1 = this.trail[i];
                    const t2 = this.trail[i + 1];

                    // Calculate thickness - thick at front (near object), thin at back (tail)
                    const thickness1 = p.map(i, 0, this.trail.length, 1, this.size * 0.4);
                    const thickness2 = p.map(i + 1, 0, this.trail.length, 1, this.size * 0.4);

                    // Calculate alpha - fades toward tail
                    const segmentAlpha = p.map(i, 0, this.trail.length, this.alpha * 0.05, this.alpha * 0.5);

                    // Calculate direction perpendicular to trail segment
                    const dx = t2.x - t1.x;
                    const dy = t2.y - t1.y;
                    const len = Math.sqrt(dx * dx + dy * dy);
                    if (len === 0) continue;

                    // Perpendicular unit vector
                    const px = -dy / len;
                    const py = dx / len;

                    // Draw outer glow layer
                    p.fill(...color, segmentAlpha * 0.3);
                    p.beginShape();
                    p.vertex(t1.x + px * thickness1 * 1.5, t1.y + py * thickness1 * 1.5);
                    p.vertex(t2.x + px * thickness2 * 1.5, t2.y + py * thickness2 * 1.5);
                    p.vertex(t2.x - px * thickness2 * 1.5, t2.y - py * thickness2 * 1.5);
                    p.vertex(t1.x - px * thickness1 * 1.5, t1.y - py * thickness1 * 1.5);
                    p.endShape(p.CLOSE);

                    // Draw main trail body
                    p.fill(...color, segmentAlpha);
                    p.beginShape();
                    p.vertex(t1.x + px * thickness1, t1.y + py * thickness1);
                    p.vertex(t2.x + px * thickness2, t2.y + py * thickness2);
                    p.vertex(t2.x - px * thickness2, t2.y - py * thickness2);
                    p.vertex(t1.x - px * thickness1, t1.y - py * thickness1);
                    p.endShape(p.CLOSE);

                    // Draw bright core
                    p.fill(...color, segmentAlpha * 1.5);
                    p.beginShape();
                    p.vertex(t1.x + px * thickness1 * 0.3, t1.y + py * thickness1 * 0.3);
                    p.vertex(t2.x + px * thickness2 * 0.3, t2.y + py * thickness2 * 0.3);
                    p.vertex(t2.x - px * thickness2 * 0.3, t2.y - py * thickness2 * 0.3);
                    p.vertex(t1.x - px * thickness1 * 0.3, t1.y - py * thickness1 * 0.3);
                    p.endShape(p.CLOSE);
                }

                // Connect last trail point to current position
                if (this.trail.length > 0) {
                    const lastTrail = this.trail[this.trail.length - 1];
                    const thickness1 = this.size * 0.4;
                    const thickness2 = this.size * 0.5;

                    const dx = this.x - lastTrail.x;
                    const dy = this.y - lastTrail.y;
                    const len = Math.sqrt(dx * dx + dy * dy);
                    if (len > 0) {
                        const px = -dy / len;
                        const py = dx / len;

                        // Outer glow
                        p.fill(...color, this.alpha * 0.2);
                        p.beginShape();
                        p.vertex(lastTrail.x + px * thickness1 * 1.5, lastTrail.y + py * thickness1 * 1.5);
                        p.vertex(this.x + px * thickness2 * 1.5, this.y + py * thickness2 * 1.5);
                        p.vertex(this.x - px * thickness2 * 1.5, this.y - py * thickness2 * 1.5);
                        p.vertex(lastTrail.x - px * thickness1 * 1.5, lastTrail.y - py * thickness1 * 1.5);
                        p.endShape(p.CLOSE);

                        // Main body
                        p.fill(...color, this.alpha * 0.6);
                        p.beginShape();
                        p.vertex(lastTrail.x + px * thickness1, lastTrail.y + py * thickness1);
                        p.vertex(this.x + px * thickness2, this.y + py * thickness2);
                        p.vertex(this.x - px * thickness2, this.y - py * thickness2);
                        p.vertex(lastTrail.x - px * thickness1, lastTrail.y - py * thickness1);
                        p.endShape(p.CLOSE);
                    }
                }

                // Draw sparkle particles along trail
                for (let i = 0; i < this.trail.length; i += 2) {
                    const t = this.trail[i];
                    const sparkleAlpha = p.map(i, 0, this.trail.length, 10, this.alpha * 0.5);
                    const sparkleSize = p.map(i, 0, this.trail.length, 2, 5);
                    const offset = p.map(i, 0, this.trail.length, 1, this.size * 0.3);
                    p.fill(...color, sparkleAlpha);
                    p.ellipse(t.x + p.random(-offset, offset), t.y + p.random(-offset, offset), sparkleSize);
                }
            }

            // Draw main object
            p.push();
            p.translate(this.x, this.y);
            p.rotate(this.rotation);

            if (this.type === 'logo') {
                this.drawLogo(this.size, this.alpha);
            } else {
                this.drawEnvelope(this.size, this.alpha);
            }

            p.pop();
        }

        drawLogo(size, alpha) {
            // Draw Groundwork logo - pyramid of foundation blocks
            // Based on the SVG: 3 blocks on bottom, 2 in middle, 1 on top
            const blockSize = size * 0.28;
            const gap = size * 0.08;
            const radius = blockSize * 0.15;

            p.rectMode(p.CENTER);
            p.noStroke();

            // Bottom layer - 3 blocks
            const bottomY = size * 0.35;
            p.fill(...COLORS.teal, alpha * 0.5);
            p.rect(-blockSize - gap, bottomY, blockSize, blockSize, radius);
            p.fill(...COLORS.teal, alpha * 0.7);
            p.rect(0, bottomY, blockSize, blockSize, radius);
            p.fill(...COLORS.teal, alpha * 0.5);
            p.rect(blockSize + gap, bottomY, blockSize, blockSize, radius);

            // Middle layer - 2 blocks
            const middleY = bottomY - blockSize - gap;
            p.fill(...COLORS.teal, alpha * 0.85);
            p.rect(-blockSize / 2 - gap / 2, middleY, blockSize, blockSize, radius);
            p.rect(blockSize / 2 + gap / 2, middleY, blockSize, blockSize, radius);

            // Top block - 1 block (brightest)
            const topY = middleY - blockSize - gap;
            p.fill(...COLORS.teal, alpha);
            p.rect(0, topY, blockSize, blockSize, radius);
        }

        drawEnvelope(size, alpha) {
            // Draw envelope icon
            const w = size;
            const h = size * 0.7;

            p.noFill();
            p.stroke(...COLORS.amber, alpha);
            p.strokeWeight(size * 0.06);
            p.strokeJoin(p.ROUND);

            // Envelope body
            p.rectMode(p.CENTER);
            p.rect(0, 0, w, h, size * 0.08);

            // Envelope flap (V shape pointing down into envelope)
            p.line(-w / 2, -h / 2, 0, h * 0.15);
            p.line(w / 2, -h / 2, 0, h * 0.15);
        }
    }

    p.setup = () => {
        const container = document.getElementById('features-canvas');
        if (!container) return;

        const canvas = p.createCanvas(container.offsetWidth, container.offsetHeight);
        canvas.parent('features-canvas');

        p.frameRate(30);
        p.pixelDensity(Math.min(p.displayDensity(), 2));

        // Create flying objects - mix of logos and envelopes
        for (let i = 0; i < objectCount; i++) {
            const type = i % 2 === 0 ? 'logo' : 'envelope';
            flyingObjects.push(new FlyingObject(type));
        }
    };

    p.draw = () => {
        // Use elevated background to match features section
        p.background(...COLORS.bgElevated);

        if (prefersReducedMotion) {
            // Static version: just show objects in place without animation
            flyingObjects.forEach(obj => {
                p.push();
                p.translate(obj.x, obj.y);
                if (obj.type === 'logo') {
                    obj.drawLogo(obj.size, obj.alpha * 0.5);
                } else {
                    obj.drawEnvelope(obj.size, obj.alpha * 0.5);
                }
                p.pop();
            });
            p.noLoop();
            return;
        }

        // Animate and draw all objects
        flyingObjects.forEach(obj => {
            obj.update();
            obj.draw();
        });
    };

    p.windowResized = () => {
        const container = document.getElementById('features-canvas');
        if (!container) return;
        p.resizeCanvas(container.offsetWidth, container.offsetHeight);
    };
};

/**
 * Pricing background sketch - Floating Geometric Shapes
 * Subtle floating diamonds/hexagons behind pricing cards
 */
const pricingSketch = (p) => {
    let shapes = [];
    const shapeCount = 15;

    class FloatingShape {
        constructor() {
            this.x = p.random(p.width);
            this.y = p.random(p.height);
            this.size = p.random(30, 80);
            this.rotation = p.random(p.TWO_PI);
            this.rotationSpeed = p.random(-0.005, 0.005);
            this.floatOffset = p.random(p.TWO_PI);
            this.floatSpeed = p.random(0.01, 0.02);
            this.floatAmplitude = p.random(10, 30);
            this.baseY = this.y;
            this.alpha = p.random(15, 35);
            this.sides = p.random() > 0.5 ? 4 : 6; // Diamond or hexagon
            this.color = p.random() > 0.7 ? COLORS.amber : COLORS.teal;
        }

        update() {
            this.rotation += this.rotationSpeed;
            this.y = this.baseY + p.sin(p.frameCount * this.floatSpeed + this.floatOffset) * this.floatAmplitude;
        }

        draw() {
            p.push();
            p.translate(this.x, this.y);
            p.rotate(this.rotation);

            p.noFill();
            p.stroke(...this.color, this.alpha);
            p.strokeWeight(1.5);

            // Draw polygon
            p.beginShape();
            for (let i = 0; i < this.sides; i++) {
                const angle = (p.TWO_PI / this.sides) * i - p.HALF_PI;
                const x = p.cos(angle) * this.size / 2;
                const y = p.sin(angle) * this.size / 2;
                p.vertex(x, y);
            }
            p.endShape(p.CLOSE);

            // Inner shape for depth
            p.stroke(...this.color, this.alpha * 0.5);
            p.beginShape();
            for (let i = 0; i < this.sides; i++) {
                const angle = (p.TWO_PI / this.sides) * i - p.HALF_PI;
                const x = p.cos(angle) * this.size / 3;
                const y = p.sin(angle) * this.size / 3;
                p.vertex(x, y);
            }
            p.endShape(p.CLOSE);

            p.pop();
        }
    }

    p.setup = () => {
        const container = document.getElementById('pricing-canvas');
        if (!container) return;

        const canvas = p.createCanvas(container.offsetWidth, container.offsetHeight);
        canvas.parent('pricing-canvas');

        p.frameRate(30);
        p.pixelDensity(Math.min(p.displayDensity(), 2));

        for (let i = 0; i < shapeCount; i++) {
            shapes.push(new FloatingShape());
        }
    };

    p.draw = () => {
        p.background(...COLORS.bgBase);

        if (prefersReducedMotion) {
            // Static version
            shapes.forEach(shape => {
                p.push();
                p.translate(shape.x, shape.y);
                p.rotate(shape.rotation);

                p.noFill();
                p.stroke(...shape.color, shape.alpha * 0.5);
                p.strokeWeight(1.5);

                p.beginShape();
                for (let i = 0; i < shape.sides; i++) {
                    const angle = (p.TWO_PI / shape.sides) * i - p.HALF_PI;
                    const x = p.cos(angle) * shape.size / 2;
                    const y = p.sin(angle) * shape.size / 2;
                    p.vertex(x, y);
                }
                p.endShape(p.CLOSE);

                p.pop();
            });
            p.noLoop();
            return;
        }

        shapes.forEach(shape => {
            shape.update();
            shape.draw();
        });
    };

    p.windowResized = () => {
        const container = document.getElementById('pricing-canvas');
        if (!container) return;
        p.resizeCanvas(container.offsetWidth, container.offsetHeight);

        // Reposition shapes that are now outside bounds
        shapes.forEach(shape => {
            if (shape.x > p.width) shape.x = p.random(p.width);
            shape.baseY = p.random(p.height);
            shape.y = shape.baseY;
        });
    };
};

/**
 * Initialize all landing page animations
 * Called from app.js when on landing page
 */
export function initAnimations() {
    initReducedMotionDetection();

    // Initialize hero animation if container exists
    if (document.getElementById('hero-canvas')) {
        new p5(heroSketch, 'hero-canvas');
    }

    // Initialize features animation if container exists
    if (document.getElementById('features-canvas')) {
        new p5(featuresSketch, 'features-canvas');
    }

    // Initialize pricing animation if container exists
    if (document.getElementById('pricing-canvas')) {
        new p5(pricingSketch, 'pricing-canvas');
    }
}

export default { initAnimations };
