<!-- Mobile Responsiveness Testing Component (Development Only) -->
@if(app()->environment(['local', 'testing']))
<div id="responsive-tester" class="responsive-tester position-fixed" style="top: 10px; left: 10px; z-index: 9999; background: rgba(0,0,0,0.9); color: white; padding: 10px; border-radius: 8px; font-family: monospace; font-size: 12px;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <strong>Responsive Test</strong>
        <button type="button" class="btn btn-sm btn-outline-light" onclick="toggleResponsiveTester()" aria-label="Toggle responsive tester">
            <i class="bi bi-arrows-angle-expand"></i>
        </button>
    </div>
    <div id="responsive-info">
        <div>Viewport: <span id="viewport-size">{{ isset($_SERVER['HTTP_USER_AGENT']) ? 'Desktop' : 'Unknown' }}</span></div>
        <div>Device: <span id="device-type">Desktop</span></div>
        <div>Breakpoint: <span id="current-breakpoint">XL</span></div>
        <div>Touch: <span id="touch-support">No</span></div>
    </div>
    <div class="mt-2">
        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="testBreakpoint('xs')">XS</button>
        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="testBreakpoint('sm')">SM</button>
        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="testBreakpoint('md')">MD</button>
        <button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="testBreakpoint('lg')">LG</button>
        <button type="button" class="btn btn-sm btn-outline-primary" onclick="testBreakpoint('xl')">XL</button>
    </div>
</div>

<style>
.responsive-tester {
    transition: all 0.3s ease;
}

.responsive-tester.minimized {
    width: auto;
}

.responsive-tester.minimized #responsive-info {
    display: none;
}

.responsive-tester.minimized .mt-2 {
    display: none;
}

/* Device frame overlays for testing */
.device-frame {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
    z-index: 9998;
    display: none;
}

.device-frame.active {
    display: block;
}

.device-frame::before {
    content: '';
    position: absolute;
    background: rgba(0, 0, 0, 0.1);
    border: 2px dashed rgba(0, 0, 0, 0.3);
}

/* iPhone frame */
.device-frame.iphone::before {
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 375px;
    height: 667px;
}

/* iPad frame */
.device-frame.ipad::before {
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 768px;
    height: 1024px;
}

/* Android frame */
.device-frame.android::before {
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 360px;
    height: 640px;
}

/* Touch indicator */
.touch-indicator {
    position: fixed;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    background: rgba(255, 165, 0, 0.8);
    pointer-events: none;
    z-index: 10000;
    display: none;
    animation: touchPulse 0.5s ease-out;
}

@keyframes touchPulse {
    0% {
        transform: scale(1);
        opacity: 1;
    }
    100% {
        transform: scale(2);
        opacity: 0;
    }
}

/* Responsive test toolbar */
.responsive-toolbar {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 10px;
    border-radius: 8px;
    z-index: 9997;
}

/* Orientation indicator */
.orientation-indicator {
    position: fixed;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(0, 0, 0, 0.8);
    color: white;
    padding: 20px;
    border-radius: 8px;
    font-size: 24px;
    z-index: 10001;
    display: none;
}
</style>

<script>
class ResponsiveTester {
    constructor() {
        this.breakpoints = {
            xs: 0,
            sm: 576,
            md: 768,
            lg: 992,
            xl: 1200
        };
        this.init();
    }

    init() {
        this.updateInfo();
        this.setupEventListeners();
        this.setupOrientationListener();
        this.setupDeviceDetection();

        // Update on resize
        let resizeTimer;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(() => {
                this.updateInfo();
            }, 250);
        });

        // Update on orientation change
        window.addEventListener('orientationchange', () => {
            setTimeout(() => {
                this.updateInfo();
                this.showOrientationChange();
            }, 500);
        });

        // Test touch support
        this.testTouchSupport();

        // Create device frames
        this.createDeviceFrames();
    }

    updateInfo() {
        const width = window.innerWidth;
        const height = window.innerHeight;

        // Update viewport size
        document.getElementById('viewport-size').textContent = `${width}x${height}px`;

        // Update current breakpoint
        const breakpoint = this.getCurrentBreakpoint(width);
        document.getElementById('current-breakpoint').textContent = breakpoint.toUpperCase();

        // Update device type
        const deviceType = this.getDeviceType(width);
        document.getElementById('device-type').textContent = deviceType;

        // Log to console for debugging
        console.log(`Viewport: ${width}x${height}, Breakpoint: ${breakpoint}, Device: ${deviceType}`);
    }

    getCurrentBreakpoint(width) {
        if (width >= this.breakpoints.xl) return 'xl';
        if (width >= this.breakpoints.lg) return 'lg';
        if (width >= this.breakpoints.md) return 'md';
        if (width >= this.breakpoints.sm) return 'sm';
        return 'xs';
    }

    getDeviceType(width) {
        if (width < 576) return 'Mobile';
        if (width < 768) return 'Mobile Large';
        if (width < 992) return 'Tablet';
        if (width < 1200) return 'Desktop Small';
        return 'Desktop';
    }

    setupEventListeners() {
        // Test touch events
        document.addEventListener('touchstart', (e) => {
            this.createTouchIndicator(e.touches[0]);
        }, { passive: true });

        document.addEventListener('click', (e) => {
            if (!('ontouchstart' in window)) {
                this.createTouchIndicator(e);
            }
        });
    }

    setupOrientationListener() {
        if (screen.orientation) {
            screen.orientation.addEventListener('change', () => {
                this.updateInfo();
            });
        }
    }

    setupDeviceDetection() {
        const ua = navigator.userAgent;

        // Detect device type
        let device = 'Desktop';
        if (/Mobile|Android|iPhone|iPad/.test(ua)) {
            if (/iPad/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1)) {
                device = 'Tablet';
            } else {
                device = 'Mobile';
            }
        }

        // Detect browser
        let browser = 'Unknown';
        if (ua.includes('Chrome')) browser = 'Chrome';
        else if (ua.includes('Firefox')) browser = 'Firefox';
        else if (ua.includes('Safari')) browser = 'Safari';
        else if (ua.includes('Edge')) browser = 'Edge';

        console.log(`Device: ${device}, Browser: ${browser}, UA: ${ua}`);
    }

    testTouchSupport() {
        const hasTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;
        document.getElementById('touch-support').textContent = hasTouch ? 'Yes' : 'No';
    }

    createDeviceFrames() {
        const frameContainer = document.createElement('div');
        frameContainer.innerHTML = `
            <div class="device-frame iphone" data-device="iphone"></div>
            <div class="device-frame ipad" data-device="ipad"></div>
            <div class="device-frame android" data-device="android"></div>
            <div class="touch-indicator" id="touch-indicator"></div>
            <div class="orientation-indicator" id="orientation-indicator">
                <i class="bi bi-phone-rotate"></i> Rotating...
            </div>
        `;
        document.body.appendChild(frameContainer);
    }

    createTouchIndicator(point) {
        const indicator = document.getElementById('touch-indicator');
        indicator.style.left = (point.clientX - 10) + 'px';
        indicator.style.top = (point.clientY - 10) + 'px';
        indicator.style.display = 'block';

        setTimeout(() => {
            indicator.style.display = 'none';
        }, 500);
    }

    showOrientationChange() {
        const indicator = document.getElementById('orientation-indicator');
        indicator.style.display = 'block';

        setTimeout(() => {
            indicator.style.display = 'none';
        }, 1000);
    }

    testBreakpoint(breakpoint) {
        const width = this.breakpoints[breakpoint];
        const tester = document.getElementById('responsive-tester');

        // Create a temporary viewport simulation
        const overlay = document.createElement('div');
        overlay.className = 'breakpoint-simulator';
        overlay.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.8);
            z-index: 9998;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        `;

        const simulator = document.createElement('div');
        simulator.style.cssText = `
            width: ${width}px;
            height: ${window.innerHeight}px;
            background: white;
            border: 2px solid #007bff;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
        `;

        const content = document.createElement('div');
        content.innerHTML = `
            <div style="padding: 20px; background: #007bff; color: white; text-align: center;">
                <h3>${breakpoint.toUpperCase()} Breakpoint (${width}px+)</h3>
                <p>Testing viewport simulation</p>
                <button onclick="this.closest('.breakpoint-simulator').remove()" style="margin-top: 10px;" class="btn btn-light">Close</button>
            </div>
            <div style="padding: 20px;">
                <p>This simulates the ${breakpoint.toUpperCase()} breakpoint:</p>
                <ul>
                    <li>Width: ${width}px and up</li>
                    <li>Device type: ${this.getDeviceType(width)}</li>
                    <li>Expected behavior: ${this.getExpectedBehavior(breakpoint)}</li>
                </ul>
            </div>
        `;

        simulator.appendChild(content);
        overlay.appendChild(simulator);
        document.body.appendChild(overlay);

        // Log test
        console.log(`Testing ${breakpoint} breakpoint (${width}px+)`);
    }

    getExpectedBehavior(breakpoint) {
        const behaviors = {
            xs: 'Mobile-first design, single column, collapsible navigation',
            sm: 'Small mobile, still single column, improved spacing',
            md: 'Tablet layout, possible multi-column, sidebar adjustment',
            lg: 'Desktop layout, full navigation, multi-column content',
            xl: 'Large desktop, maximum content width, enhanced features'
        };
        return behaviors[breakpoint] || 'Unknown behavior';
    }

    // Public methods for testing
    showDeviceFrame(device) {
        document.querySelectorAll('.device-frame').forEach(frame => {
            frame.classList.remove('active');
        });
        document.querySelector(`.device-frame.${device}`)?.classList.add('active');
    }

    hideDeviceFrames() {
        document.querySelectorAll('.device-frame').forEach(frame => {
            frame.classList.remove('active');
        });
    }

    runResponsivenessTests() {
        console.log('Running responsiveness tests...');

        const tests = [
            this.testMobileNavigation,
            this.testTableResponsive,
            this.testFormResponsive,
            this.testImageOptimization,
            this.testTouchTargets
        ];

        tests.forEach((test, index) => {
            setTimeout(() => {
                test.call(this);
            }, index * 1000);
        });
    }

    testMobileNavigation() {
        const navbar = document.querySelector('.navbar');
        const toggler = document.querySelector('.navbar-toggler');

        console.log('Testing mobile navigation...');
        console.log('Navbar found:', !!navbar);
        console.log('Toggler found:', !!toggler);
        console.log('Toggler visible:', toggler ? window.getComputedStyle(toggler).display !== 'none' : false);
    }

    testTableResponsive() {
        const tables = document.querySelectorAll('.table');

        console.log('Testing table responsiveness...');
        console.log('Tables found:', tables.length);

        tables.forEach((table, index) => {
            const isResponsive = table.closest('.table-responsive') ||
                               table.closest('.responsive-table');
            console.log(`Table ${index + 1} responsive:`, !!isResponsive);
        });
    }

    testFormResponsive() {
        const forms = document.querySelectorAll('form');

        console.log('Testing form responsiveness...');
        console.log('Forms found:', forms.length);

        forms.forEach((form, index) => {
            const hasResponsiveClass = form.classList.contains('needs-validation') ||
                                     form.querySelector('.form-floating');
            console.log(`Form ${index + 1} has responsive features:`, !!hasResponsiveClass);
        });
    }

    testImageOptimization() {
        const images = document.querySelectorAll('img');

        console.log('Testing image optimization...');
        console.log('Images found:', images.length);

        images.forEach((img, index) => {
            const hasLazyLoad = img.hasAttribute('loading') || img.hasAttribute('data-src');
            const hasResponsiveClass = img.classList.contains('img-fluid');
            console.log(`Image ${index + 1} optimized:`, hasLazyLoad || hasResponsiveClass);
        });
    }

    testTouchTargets() {
        const touchElements = document.querySelectorAll('button, a, input, select, textarea');

        console.log('Testing touch targets...');
        console.log('Touch elements found:', touchElements.length);

        const smallTargets = Array.from(touchElements).filter(element => {
            const rect = element.getBoundingClientRect();
            return rect.width < 44 || rect.height < 44; // WCAG minimum
        });

        console.log('Small touch targets (<44px):', smallTargets.length);
        if (smallTargets.length > 0) {
            console.warn('Small touch targets found:', smallTargets);
        }
    }
}

// Initialize responsive tester
const responsiveTester = new ResponsiveTester();

// Global functions for onclick handlers
function toggleResponsiveTester() {
    const tester = document.getElementById('responsive-tester');
    tester.classList.toggle('minimized');
}

function testBreakpoint(breakpoint) {
    responsiveTester.testBreakpoint(breakpoint);
}

// Make available globally for debugging
window.responsiveTester = responsiveTester;

// Auto-run tests in development
if (window.location.hostname === 'localhost') {
    setTimeout(() => {
        console.log('Running automatic responsiveness tests...');
        responsiveTester.runResponsivenessTests();
    }, 3000);
}
</script>
@endif