<!-- Performance Optimization Component -->

<!-- Lazy Loading Images -->
<script>
// Image lazy loading with intersection observer
document.addEventListener('DOMContentLoaded', function() {
    const imageObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const img = entry.target;

                if (img.dataset.src) {
                    img.src = img.dataset.src;
                    img.onload = () => {
                        img.classList.add('loaded');
                        img.classList.remove('loading');
                    };
                    img.classList.add('loading');
                }

                if (img.dataset.srcset) {
                    img.srcset = img.dataset.srcset;
                }

                observer.unobserve(img);
            }
        });
    }, {
        rootMargin: '50px 0px',
        threshold: 0.1
    });

    // Observe all images with data-src
    document.querySelectorAll('img[data-src]').forEach(img => {
        imageObserver.observe(img);
    });
});
</script>

<!-- Critical CSS Inlining -->
<style>
/* Critical above-the-fold styles for immediate rendering */
.critical-css {
    /* Bootstrap core critical styles */
    .container-fluid { max-width: 100%; padding: 0 15px; }
    .row { display: flex; flex-wrap: wrap; margin: 0 -15px; }
    .col-md-9, .col-md-3, .col-lg-10, .col-lg-2 { position: relative; width: 100%; padding: 0 15px; }

    /* Navigation critical styles */
    .navbar { position: relative; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; padding: 0.5rem 1rem; }
    .navbar-dark { color: rgba(255,255,255,.55); }
    .navbar-brand { padding-top: 0.3125rem; padding-bottom: 0.3125rem; margin-right: 1rem; font-size: 1.25rem; text-decoration: none; }
    .sticky-top { position: sticky; top: 0; z-index: 1020; }
    .bg-primary { background-color: #0d6efd!important; }
    .text-white { color: #fff!important; }

    /* Loading placeholder styles */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Typography critical styles */
    h1, h2, h3, h4, h5, h6 { margin-top: 0; margin-bottom: 0.5rem; font-weight: 500; line-height: 1.2; }
    h1 { font-size: 2.5rem; }
    h2 { font-size: 2rem; }
    h3 { font-size: 1.75rem; }
    h4 { font-size: 1.5rem; }
    h5 { font-size: 1.25rem; }
    h6 { font-size: 1rem; }

    /* Utility critical styles */
    .d-flex { display: flex!important; }
    .align-items-center { align-items: center!important; }
    .me-auto { margin-right: auto!important; }
    .mb-2 { margin-bottom: 0.5rem!important; }
    .mb-lg-0 { margin-bottom: 0!important; }
    .d-none { display: none!important; }
    .d-md-block { display: block!important; }

    @media (min-width: 768px) {
        .col-md-3 { flex: 0 0 25%; max-width: 25%; }
        .col-md-9 { flex: 0 0 75%; max-width: 75%; }
    }

    @media (min-width: 992px) {
        .col-lg-2 { flex: 0 0 16.666667%; max-width: 16.666667%; }
        .col-lg-10 { flex: 0 0 83.333333%; max-width: 83.333333%; }
        .d-lg-block { display: block!important; }
    }
}

/* Loading states */
.loading-skeleton {
    background: linear-gradient(90deg, #f8f9fa 25%, #e9ecef 50%, #f8f9fa 75%);
    background-size: 200% 100%;
    animation: skeletonLoading 1.5s infinite;
}

@keyframes skeletonLoading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Image loading styles */
img.loading {
    background: #f8f9fa;
    border: 1px solid #dee2e6;
}

img.loaded {
    opacity: 1;
    transition: opacity 0.3s ease;
}

/* Code splitting loading indicator */
.chunk-loading {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--bs-primary), var(--bs-secondary), var(--bs-primary));
    background-size: 200% 100%;
    animation: chunkLoading 1.5s infinite;
    z-index: 9999;
}

@keyframes chunkLoading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>

<!-- Performance Monitoring -->
<script>
class PerformanceMonitor {
    constructor() {
        this.metrics = {
            navigation: {},
            paint: {},
            memory: {},
            resources: []
        };
        this.init();
    }

    init() {
        // Monitor page load performance
        this.monitorPageLoad();

        // Monitor resource loading
        this.monitorResources();

        // Monitor user interactions
        this.monitorUserInteractions();

        // Monitor memory usage (if available)
        this.monitorMemory();

        // Setup performance budget warnings
        this.setupPerformanceBudget();
    }

    monitorPageLoad() {
        if ('performance' in window && 'getEntriesByType' in performance) {
            // Navigation timing
            const navigationEntries = performance.getEntriesByType('navigation');
            if (navigationEntries.length > 0) {
                const nav = navigationEntries[0];
                this.metrics.navigation = {
                    dns: nav.domainLookupEnd - nav.domainLookupStart,
                    tcp: nav.connectEnd - nav.connectStart,
                    ssl: nav.secureConnectionStart > 0 ? nav.connectEnd - nav.secureConnectionStart : 0,
                    ttfb: nav.responseStart - nav.requestStart,
                    download: nav.responseEnd - nav.responseStart,
                    domParse: nav.domContentLoadedEventStart - nav.responseEnd,
                    domReady: nav.domContentLoadedEventEnd - nav.domContentLoadedEventStart,
                    loadComplete: nav.loadEventEnd - nav.loadEventStart,
                    total: nav.loadEventEnd - nav.navigationStart
                };
            }

            // Paint timing
            const paintEntries = performance.getEntriesByType('paint');
            paintEntries.forEach(entry => {
                this.metrics.paint[entry.name] = entry.startTime;
            });
        }
    }

    monitorResources() {
        if ('PerformanceObserver' in window) {
            const resourceObserver = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (entry.entryType === 'resource') {
                        this.metrics.resources.push({
                            name: entry.name,
                            type: this.getResourceType(entry.name),
                            size: entry.transferSize || 0,
                            duration: entry.duration,
                            startTime: entry.startTime
                        });
                    }
                });
            });

            resourceObserver.observe({ entryTypes: ['resource'] });
        }
    }

    getResourceType(url) {
        const extension = url.split('.').pop()?.toLowerCase();
        const resourceTypes = {
            'js': 'javascript',
            'css': 'stylesheet',
            'png': 'image',
            'jpg': 'image',
            'jpeg': 'image',
            'gif': 'image',
            'svg': 'image',
            'webp': 'image',
            'woff': 'font',
            'woff2': 'font',
            'ttf': 'font'
        };
        return resourceTypes[extension] || 'other';
    }

    monitorUserInteractions() {
        // Track First Input Delay (FID)
        if ('PerformanceEventTiming' in window && 'PerformanceObserver' in window) {
            const fidObserver = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (entry.name === 'first-input') {
                        this.metrics.firstInputDelay = entry.processingStart - entry.startTime;
                        this.checkPerformanceThresholds();
                    }
                });
            });

            fidObserver.observe({ entryTypes: ['first-input'] });
        }

        // Track Cumulative Layout Shift (CLS)
        let clsValue = 0;
        if ('LayoutShift' in window && 'PerformanceObserver' in window) {
            const clsObserver = new PerformanceObserver((list) => {
                list.getEntries().forEach((entry) => {
                    if (!entry.hadRecentInput) {
                        clsValue += entry.value;
                        this.metrics.cumulativeLayoutShift = clsValue;
                    }
                });
            });

            clsObserver.observe({ entryTypes: ['layout-shift'] });
        }

        // Track Largest Contentful Paint (LCP)
        if ('LargestContentfulPaint' in window && 'PerformanceObserver' in window) {
            const lcpObserver = new PerformanceObserver((list) => {
                const entries = list.getEntries();
                const lastEntry = entries[entries.length - 1];
                this.metrics.largestContentfulPaint = lastEntry.renderTime || lastEntry.loadTime;
            });

            lcpObserver.observe({ entryTypes: ['largest-contentful-paint'] });
        }
    }

    monitorMemory() {
        if ('memory' in performance) {
            setInterval(() => {
                this.metrics.memory = {
                    used: performance.memory.usedJSHeapSize,
                    total: performance.memory.totalJSHeapSize,
                    limit: performance.memory.jsHeapSizeLimit,
                    percentage: (performance.memory.usedJSHeapSize / performance.memory.jsHeapSizeLimit) * 100
                };
            }, 5000); // Check every 5 seconds
        }
    }

    setupPerformanceBudget() {
        // Define performance budgets
        this.budget = {
            totalSize: 2500000, // 2.5MB
            scriptSize: 500000, // 500KB
            styleSize: 200000, // 200KB
            imageSize: 1500000, // 1.5MB
            fontSize: 200000, // 200KB
            requestCount: 50,
            lcp: 2500, // 2.5 seconds
            fid: 100, // 100ms
            cls: 0.1
        };

        // Check budgets after page load
        window.addEventListener('load', () => {
            setTimeout(() => {
                this.checkPerformanceBudgets();
            }, 1000);
        });
    }

    checkPerformanceBudgets() {
        const warnings = [];

        // Check total size
        const totalSize = this.metrics.resources.reduce((sum, resource) => sum + resource.size, 0);
        if (totalSize > this.budget.totalSize) {
            warnings.push(`Total page size (${Math.round(totalSize / 1024)}KB) exceeds budget (${Math.round(this.budget.totalSize / 1024)}KB)`);
        }

        // Check resource type sizes
        const resourceSizes = {};
        this.metrics.resources.forEach(resource => {
            resourceSizes[resource.type] = (resourceSizes[resource.type] || 0) + resource.size;
        });

        Object.keys(resourceSizes).forEach(type => {
            const budgetKey = `${type}Size`;
            if (this.budget[budgetKey] && resourceSizes[type] > this.budget[budgetKey]) {
                warnings.push(`${type} size (${Math.round(resourceSizes[type] / 1024)}KB) exceeds budget (${Math.round(this.budget[budgetKey] / 1024)}KB)`);
            }
        });

        // Check request count
        if (this.metrics.resources.length > this.budget.requestCount) {
            warnings.push(`Request count (${this.metrics.resources.length}) exceeds budget (${this.budget.requestCount})`);
        }

        // Check Core Web Vitals
        if (this.metrics.largestContentfulPaint > this.budget.lcp) {
            warnings.push(`LCP (${Math.round(this.metrics.largestContentfulPaint)}ms) exceeds budget (${this.budget.lcp}ms)`);
        }

        if (this.metrics.firstInputDelay > this.budget.fid) {
            warnings.push(`FID (${Math.round(this.metrics.firstInputDelay)}ms) exceeds budget (${this.budget.fid}ms)`);
        }

        if (this.metrics.cumulativeLayoutShift > this.budget.cls) {
            warnings.push(`CLS (${this.metrics.cumulativeLayoutShift.toFixed(3)}) exceeds budget (${this.budget.cls})`);
        }

        // Log warnings to console in development
        if (warnings.length > 0 && window.location.hostname === 'localhost') {
            console.warn('Performance Budget Warnings:', warnings);
        }

        return warnings;
    }

    checkPerformanceThresholds() {
        const warnings = this.checkPerformanceBudgets();
        if (warnings.length > 0) {
            // Show performance warnings to developers
            if (typeof NotificationManager !== 'undefined') {
                warnings.forEach(warning => {
                    NotificationManager.show(`Performance: ${warning}`, 'warning');
                });
            }
        }
    }

    getMetrics() {
        return {
            ...this.metrics,
            warnings: this.checkPerformanceBudgets()
        };
    }

    // Export metrics for analysis
    exportMetrics() {
        const dataStr = JSON.stringify(this.getMetrics(), null, 2);
        const dataUri = 'data:application/json;charset=utf-8,'+ encodeURIComponent(dataStr);

        const exportFileDefaultName = `performance-metrics-${new Date().toISOString().split('T')[0]}.json`;

        const linkElement = document.createElement('a');
        linkElement.setAttribute('href', dataUri);
        linkElement.setAttribute('download', exportFileDefaultName);
        linkElement.click();
    }
}

// Initialize performance monitoring
const performanceMonitor = new PerformanceMonitor();

// Add performance export functionality to dev tools
if (window.location.hostname === 'localhost') {
    window.performanceMonitor = performanceMonitor;
    console.log('Performance monitoring initialized. Use performanceMonitor.getMetrics() to view data or performanceMonitor.exportMetrics() to export.');
}

// Preload critical resources
function preloadCriticalResources() {
    const criticalResources = [
        { href: '/vendor/bootstrap/css/bootstrap.min.css', as: 'style' },
        { href: '/vendor/bootstrap/js/bootstrap.bundle.min.js', as: 'script' },
        { href: '/css/app.css', as: 'style' },
        { href: '/js/app.js', as: 'script' }
    ];

    criticalResources.forEach(resource => {
        const link = document.createElement('link');
        link.rel = 'preload';
        link.href = resource.href;
        link.as = resource.as;
        if (resource.as === 'style') {
            link.onload = function() { this.rel = 'stylesheet'; };
        }
        document.head.appendChild(link);
    });
}

// Prefetch next likely pages
function prefetchNextPages() {
    const likelyPages = [
        '/dashboard',
        '/employees',
        '/payroll',
        '/reports'
    ];

    likelyPages.forEach(page => {
        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = page;
        document.head.appendChild(link);
    });
}

// Initialize performance optimizations
document.addEventListener('DOMContentLoaded', function() {
    preloadCriticalResources();

    // Prefetch after page load
    window.addEventListener('load', function() {
        setTimeout(prefetchNextPages, 2000);
    });
});
</script>

<!-- Service Worker Registration for PWA capabilities -->
<script>
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js')
            .then(function(registration) {
                console.log('SW registered: ', registration);
            })
            .catch(function(registrationError) {
                console.log('SW registration failed: ', registrationError);
            });
    });
}
</script>