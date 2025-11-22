@php
    $placeholder = $placeholder ?? 'Search employees, payroll, leave...';
    $showFilters = $showFilters ?? true;
    $showHistory = $showHistory ?? true;
    $showSuggestions = $showSuggestions ?? true;
    $compact = $compact ?? false;
@endphp

<!-- Advanced Search Component -->
<div class="advanced-search-wrapper">
    <form class="advanced-search-form" role="search" method="GET" action="{{ url('/search') }}">
        <div class="input-group {{ $compact ? 'input-group-sm' : '' }}">
            <span class="input-group-text bg-transparent border-end-0">
                <i class="bi bi-search"></i>
            </span>

            <input type="text"
                   class="form-control border-start-0 {{ $compact ? 'form-control-sm' : '' }}"
                   name="q"
                   id="advanced-search-input"
                   placeholder="{{ $placeholder }}"
                   value="{{ request('q') }}"
                   autocomplete="off"
                   aria-label="{{ $placeholder }}">

            @if($showFilters)
            <button type="button"
                    class="btn btn-outline-secondary dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                    title="Search filters">
                <i class="bi bi-funnel"></i>
            </button>

            <!-- Search Filters Dropdown -->
            <div class="dropdown-menu dropdown-menu-end p-3" style="width: 300px;">
                <h6 class="dropdown-header">Search Filters</h6>

                <!-- Model Filter -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Search in:</label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="filter-employees" value="employees" checked>
                        <label class="form-check-label" for="filter-employees">Employees</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="filter-payroll" value="payroll" checked>
                        <label class="form-check-label" for="filter-payroll">Payroll</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="filter-leaves" value="leaves" checked>
                        <label class="form-check-label" for="filter-leaves">Leave</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="checkbox" id="filter-attendance" value="attendance" checked>
                        <label class="form-check-label" for="filter-attendance">Attendance</label>
                    </div>
                </div>

                <!-- Date Range Filter -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Date Range:</label>
                    <select class="form-select form-select-sm" id="date-filter">
                        <option value="">Any time</option>
                        <option value="today">Today</option>
                        <option value="week">This week</option>
                        <option value="month">This month</option>
                        <option value="year">This year</option>
                        <option value="custom">Custom range</option>
                    </select>
                </div>

                <!-- Custom Date Range (hidden by default) -->
                <div class="mb-3" id="custom-date-range" style="display: none;">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">From:</label>
                            <input type="date" class="form-control form-control-sm" id="date-from">
                        </div>
                        <div class="col-6">
                            <label class="form-label">To:</label>
                            <input type="date" class="form-control form-control-sm" id="date-to">
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="mb-3">
                    <label class="form-label fw-semibold">Status:</label>
                    <select class="form-select form-select-sm" id="status-filter">
                        <option value="">Any status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                        <option value="processed">Processed</option>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-primary btn-sm" id="apply-filters">
                        Apply Filters
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" id="clear-filters">
                        Clear Filters
                    </button>
                </div>
            </div>
            @endif

            @if($showHistory)
            <button type="button"
                    class="btn btn-outline-secondary"
                    id="search-history-toggle"
                    title="Search history">
                <i class="bi bi-clock-history"></i>
            </button>
            @endif

            <button type="submit" class="btn btn-primary">
                Search
            </button>
        </div>
    </form>

    <!-- Search Suggestions Dropdown -->
    <div id="search-suggestions" class="search-suggestions" style="display: none;">
        <div class="suggestions-header">
            <small class="text-muted">Suggestions</small>
        </div>
        <div id="suggestions-list">
            <!-- Suggestions will be populated here -->
        </div>
    </div>

    <!-- Search History Dropdown -->
    @if($showHistory)
    <div id="search-history" class="search-history" style="display: none;">
        <div class="history-header">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">Recent Searches</small>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-history">
                    Clear
                </button>
            </div>
        </div>
        <div id="history-list">
            <!-- History will be populated here -->
        </div>
    </div>
    @endif

    <!-- Quick Search Shortcuts -->
    <div class="search-shortcuts mt-2">
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary search-shortcut" data-search="pending leaves">
                Pending Leaves
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary search-shortcut" data-search="overtime this week">
                Overtime This Week
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary search-shortcut" data-search="salary processed">
                Processed Salary
            </button>
            <button type="button" class="btn btn-sm btn-outline-primary search-shortcut" data-search="new hires">
                New Hires
            </button>
        </div>
    </div>
</div>

<!-- Search Results Container -->
<div id="search-results-container" class="search-results-container" style="display: none;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Search Results</h5>
        <button type="button" class="btn btn-sm btn-outline-secondary" id="close-search-results">
            <i class="bi bi-x"></i>
        </button>
    </div>
    <div id="search-results">
        <!-- Results will be populated here -->
    </div>
</div>

<!-- Advanced Search Styles -->
<style>
.advanced-search-wrapper {
    position: relative;
}

.search-suggestions,
.search-history {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: white;
    border: 1px solid #dee2e6;
    border-top: none;
    border-radius: 0 0 0.375rem 0.375rem;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    max-height: 300px;
    overflow-y: auto;
    z-index: 1050;
}

.suggestions-header,
.history-header {
    padding: 8px 12px;
    border-bottom: 1px solid #f1f3f4;
    background: #f8f9fa;
}

.suggestion-item,
.history-item {
    padding: 10px 12px;
    cursor: pointer;
    transition: background-color 0.2s;
    border-bottom: 1px solid #f1f3f4;
}

.suggestion-item:hover,
.history-item:hover {
    background-color: #f8f9fa;
}

.suggestion-item:last-child,
.history-item:last-child {
    border-bottom: none;
}

.suggestion-text {
    font-weight: 500;
    color: #333;
}

.suggestion-type {
    font-size: 12px;
    color: #6c757d;
    text-transform: uppercase;
}

.search-results-container {
    background: white;
    border-radius: 0.375rem;
    padding: 20px;
    margin-top: 20px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.search-results-section {
    margin-bottom: 30px;
}

.search-results-section:last-child {
    margin-bottom: 0;
}

.search-results-header {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
    border-bottom: 2px solid #f1f3f4;
    padding-bottom: 8px;
}

.search-results-icon {
    margin-right: 10px;
    font-size: 18px;
}

.search-results-title {
    font-weight: 600;
    color: #333;
    margin: 0;
}

.search-results-count {
    margin-left: auto;
    background: #007bff;
    color: white;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.search-result-item {
    padding: 12px 15px;
    border: 1px solid #f1f3f4;
    border-radius: 6px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.2s;
}

.search-result-item:hover {
    background-color: #f8f9fa;
    border-color: #007bff;
    transform: translateY(-1px);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.search-result-item:last-child {
    margin-bottom: 0;
}

.search-result-title {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.search-result-description {
    color: #666;
    font-size: 14px;
    margin-bottom: 8px;
}

.search-result-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    font-size: 12px;
    color: #999;
}

.search-result-highlight {
    display: flex;
    gap: 10px;
    margin-top: 8px;
}

.search-highlight-item {
    background: #f1f3f4;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 11px;
}

.search-shortcuts {
    margin-top: 8px;
}

.search-shortcut {
    font-size: 12px;
    padding: 4px 8px;
    border-radius: 12px;
}

.search-shortcut:hover {
    background-color: #007bff;
    border-color: #007bff;
}

/* Loading states */
.search-loading {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

.search-loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

.search-no-results {
    text-align: center;
    padding: 40px 20px;
    color: #6c757d;
}

.search-no-results i {
    font-size: 48px;
    margin-bottom: 15px;
    opacity: 0.5;
}

/* Dark mode */
[data-bs-theme="dark"] .search-suggestions,
[data-bs-theme="dark"] .search-history {
    background: #2d3748;
    border-color: #4a5568;
}

[data-bs-theme="dark"] .suggestion-item:hover,
[data-bs-theme="dark"] .history-item:hover {
    background-color: #4a5568;
}

[data-bs-theme="dark"] .search-results-container {
    background: #2d3748;
    color: #e2e8f0;
}

[data-bs-theme="dark"] .search-result-item {
    background: #1a202c;
    border-color: #4a5568;
    color: #e2e8f0;
}

[data-bs-theme="dark"] .search-result-item:hover {
    background-color: #4a5568;
    border-color: #007bff;
}

[data-bs-theme="dark"] .search-result-title {
    color: #e2e8f0;
}

[data-bs-theme="dark"] .search-result-description {
    color: #a0aec0;
}

/* Responsive */
@media (max-width: 768px) {
    .search-suggestions,
    .search-history {
        left: -10px;
        right: -10px;
    }

    .search-results-container {
        margin: 10px;
        padding: 15px;
    }

    .search-shortcuts .d-flex {
        overflow-x: auto;
        white-space: nowrap;
        padding-bottom: 5px;
    }
}
</style>

<!-- Advanced Search JavaScript -->
<script>
class AdvancedSearchManager {
    constructor() {
        this.searchInput = document.getElementById('advanced-search-input');
        this.suggestions = document.getElementById('search-suggestions');
        this.history = document.getElementById('search-history');
        this.resultsContainer = document.getElementById('search-results-container');
        this.results = document.getElementById('search-results');
        this.currentQuery = '';
        this.searchTimeout = null;
        this.minQueryLength = 2;

        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadSearchHistory();
        this.setupKeyboardNavigation();
    }

    setupEventListeners() {
        // Search input events
        this.searchInput?.addEventListener('input', (e) => {
            this.handleInputChange(e.target.value);
        });

        this.searchInput?.addEventListener('focus', () => {
            this.showSuggestions();
        });

        this.searchInput?.addEventListener('blur', () => {
            // Delay hiding to allow click events
            setTimeout(() => {
                this.hideSuggestions();
                this.hideHistory();
            }, 200);
        });

        // Form submission
        document.querySelector('.advanced-search-form')?.addEventListener('submit', (e) => {
            e.preventDefault();
            this.performSearch();
        });

        // Filter events
        document.getElementById('date-filter')?.addEventListener('change', (e) => {
            const customRange = document.getElementById('custom-date-range');
            customRange.style.display = e.target.value === 'custom' ? 'block' : 'none';
        });

        document.getElementById('apply-filters')?.addEventListener('click', () => {
            this.applyFilters();
        });

        document.getElementById('clear-filters')?.addEventListener('click', () => {
            this.clearFilters();
        });

        // History toggle
        document.getElementById('search-history-toggle')?.addEventListener('click', () => {
            this.toggleHistory();
        });

        document.getElementById('clear-history')?.addEventListener('click', () => {
            this.clearHistory();
        });

        // Search shortcuts
        document.querySelectorAll('.search-shortcut').forEach(button => {
            button.addEventListener('click', () => {
                const search = button.dataset.search;
                this.searchInput.value = search;
                this.performSearch();
            });
        });

        // Close results
        document.getElementById('close-search-results')?.addEventListener('click', () => {
            this.hideResults();
        });

        // Click outside to close dropdowns
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.advanced-search-wrapper')) {
                this.hideSuggestions();
                this.hideHistory();
            }
        });
    }

    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            if (e.key === '/' && !this.searchInput.matches(':focus')) {
                e.preventDefault();
                this.searchInput.focus();
            }

            if (e.key === 'Escape') {
                this.hideSuggestions();
                this.hideHistory();
                this.hideResults();
            }
        });
    }

    handleInputChange(query) {
        this.currentQuery = query;

        clearTimeout(this.searchTimeout);

        if (query.length < this.minQueryLength) {
            this.hideSuggestions();
            return;
        }

        this.searchTimeout = setTimeout(() => {
            this.getSuggestions(query);
        }, 300);
    }

    async getSuggestions(query) {
        try {
            const response = await fetch(`/api/search/suggestions?q=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) return;

            const suggestions = await response.json();
            this.showSuggestions(query, suggestions);

        } catch (error) {
            console.error('Error getting suggestions:', error);
        }
    }

    showSuggestions(query = null, suggestions = []) {
        if (!this.suggestions) return;

        if (suggestions.length === 0 && !query) {
            this.hideSuggestions();
            return;
        }

        let html = '';

        if (suggestions.length > 0) {
            html = suggestions.map(suggestion => `
                <div class="suggestion-item" onclick="advancedSearch.selectSuggestion('${this.escapeHtml(suggestion)}')">
                    <div class="suggestion-text">
                        <i class="bi bi-search me-2"></i>
                        ${this.highlightMatch(suggestion, query)}
                    </div>
                </div>
            `).join('');
        } else if (query && query.length >= this.minQueryLength) {
            html = `
                <div class="suggestion-item" onclick="advancedSearch.performSearch()">
                    <div class="suggestion-text">
                        <i class="bi bi-search me-2"></i>
                        Search for "${this.escapeHtml(query)}"
                    </div>
                </div>
            `;
        }

        this.suggestions.querySelector('#suggestions-list').innerHTML = html;
        this.suggestions.style.display = 'block';
        this.hideHistory();
    }

    hideSuggestions() {
        if (this.suggestions) {
            this.suggestions.style.display = 'none';
        }
    }

    toggleHistory() {
        if (this.history.style.display === 'block') {
            this.hideHistory();
        } else {
            this.showHistory();
        }
    }

    showHistory() {
        if (!this.history) return;

        this.loadSearchHistory();
        this.history.style.display = 'block';
        this.hideSuggestions();
    }

    hideHistory() {
        if (this.history) {
            this.history.style.display = 'none';
        }
    }

    async loadSearchHistory() {
        try {
            const response = await fetch('/api/search/history', {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) return;

            const history = await response.json();
            this.renderHistory(history);

        } catch (error) {
            console.error('Error loading search history:', error);
        }
    }

    renderHistory(history) {
        if (!history || history.length === 0) {
            this.history.querySelector('#history-list').innerHTML = `
                <div class="text-center p-3 text-muted">
                    <small>No recent searches</small>
                </div>
            `;
            return;
        }

        const html = history.map(item => `
            <div class="history-item" onclick="advancedSearch.selectHistoryItem('${this.escapeHtml(item)}')">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <i class="bi bi-clock-history me-2"></i>
                        ${this.escapeHtml(item)}
                    </div>
                    <small class="text-muted">Recent</small>
                </div>
            </div>
        `).join('');

        this.history.querySelector('#history-list').innerHTML = html;
    }

    async clearHistory() {
        try {
            const response = await fetch('/api/search/history', {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content'),
                    'Accept': 'application/json'
                }
            });

            if (response.ok) {
                this.renderHistory([]);
            }
        } catch (error) {
            console.error('Error clearing search history:', error);
        }
    }

    selectSuggestion(suggestion) {
        this.searchInput.value = suggestion;
        this.hideSuggestions();
        this.performSearch();
    }

    selectHistoryItem(query) {
        this.searchInput.value = query;
        this.hideHistory();
        this.performSearch();
    }

    async performSearch() {
        const query = this.searchInput.value.trim();
        if (!query) return;

        this.hideSuggestions();
        this.hideHistory();
        this.showLoading();

        try {
            const filters = this.getActiveFilters();
            const params = new URLSearchParams({
                q: query,
                ...filters
            });

            const response = await fetch(`/api/search?${params}`, {
                headers: {
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) throw new Error('Search failed');

            const data = await response.json();
            this.renderResults(data);

        } catch (error) {
            console.error('Search error:', error);
            this.showError();
        }
    }

    getActiveFilters() {
        const filters = {};

        // Model filters
        const modelFilters = [];
        document.querySelectorAll('[id^="filter-"]:checked').forEach(checkbox => {
            modelFilters.push(checkbox.value);
        });
        if (modelFilters.length > 0) {
            filters.models = modelFilters.join(',');
        }

        // Date filter
        const dateFilter = document.getElementById('date-filter')?.value;
        if (dateFilter) {
            filters.date_range = dateFilter;
        }

        // Custom date range
        const dateFrom = document.getElementById('date-from')?.value;
        const dateTo = document.getElementById('date-to')?.value;
        if (dateFrom && dateTo) {
            filters.date_from = dateFrom;
            filters.date_to = dateTo;
        }

        // Status filter
        const statusFilter = document.getElementById('status-filter')?.value;
        if (statusFilter) {
            filters.status = statusFilter;
        }

        return filters;
    }

    applyFilters() {
        if (this.currentQuery) {
            this.performSearch();
        }
    }

    clearFilters() {
        // Reset all filters
        document.querySelectorAll('[id^="filter-"]').forEach(checkbox => {
            checkbox.checked = true;
        });

        document.getElementById('date-filter').value = '';
        document.getElementById('custom-date-range').style.display = 'none';
        document.getElementById('date-from').value = '';
        document.getElementById('date-to').value = '';
        document.getElementById('status-filter').value = '';

        if (this.currentQuery) {
            this.performSearch();
        }
    }

    showLoading() {
        this.resultsContainer.style.display = 'block';
        this.results.innerHTML = `
            <div class="search-loading">
                <i class="bi bi-arrow-clockwise"></i>
                <p>Searching...</p>
            </div>
        `;
    }

    renderResults(data) {
        this.resultsContainer.style.display = 'block';

        if (!data.results || Object.keys(data.results).length === 0) {
            this.showNoResults();
            return;
        }

        let html = '';
        let totalResults = 0;

        for (const [type, results] of Object.entries(data.results)) {
            if (results.length > 0) {
                html += this.renderResultsSection(type, results);
                totalResults += results.length;
            }
        }

        this.results.innerHTML = html;
        document.querySelector('.search-results-header')?.insertAdjacentHTML('beforeend',
            `<span class="badge bg-primary ms-2">${totalResults} results</span>`
        );
    }

    renderResultsSection(type, results) {
        const sectionConfig = this.getSectionConfig(type);

        let itemsHtml = results.map(item => `
            <div class="search-result-item" onclick="window.location.href='${item.url}'">
                <div class="d-flex align-items-start">
                    <div class="me-3">
                        <i class="${sectionConfig.icon} ${sectionConfig.color}"></i>
                    </div>
                    <div class="flex-grow-1">
                        <div class="search-result-title">${this.escapeHtml(item.title)}</div>
                        ${item.description ? `<div class="search-result-description">${this.escapeHtml(item.description)}</div>` : ''}

                        ${item.highlight && Object.keys(item.highlight).length > 0 ? `
                            <div class="search-result-highlight">
                                ${Object.entries(item.highlight).map(([label, value]) => `
                                    <span class="search-highlight-item">${label}: ${this.escapeHtml(value)}</span>
                                `).join('')}
                            </div>
                        ` : ''}

                        <div class="search-result-meta">
                            <span><i class="bi bi-folder"></i> ${sectionConfig.label}</span>
                            ${item.created_at ? `<span><i class="bi bi-clock"></i> ${this.formatDate(item.created_at)}</span>` : ''}
                        </div>
                    </div>
                </div>
            </div>
        `).join('');

        return `
            <div class="search-results-section">
                <div class="search-results-header">
                    <div class="search-results-icon">
                        <i class="${sectionConfig.icon} ${sectionConfig.color}"></i>
                    </div>
                    <h6 class="search-results-title">${sectionConfig.label}</h6>
                    <span class="search-results-count">${results.length}</span>
                </div>
                <div class="search-results-list">
                    ${itemsHtml}
                </div>
                ${results.length >= 5 ? `
                    <div class="text-center mt-3">
                        <a href="/search?q=${encodeURIComponent(this.currentQuery)}&models=${type}" class="btn btn-sm btn-outline-primary">
                            View All ${sectionConfig.label}
                        </a>
                    </div>
                ` : ''}
            </div>
        `;
    }

    getSectionConfig(type) {
        const configs = {
            employees: { label: 'Employees', icon: 'bi-people', color: 'text-primary' },
            payroll: { label: 'Payroll Records', icon: 'bi-cash-stack', color: 'text-success' },
            leaves: { label: 'Leave Requests', icon: 'bi-calendar-x', color: 'text-warning' },
            attendance: { label: 'Attendance', icon: 'bi-clock-history', color: 'text-info' },
            users: { label: 'Users', icon: 'bi-person', color: 'text-secondary' }
        };

        return configs[type] || { label: type, icon: 'bi-file-text', color: 'text-secondary' };
    }

    showNoResults() {
        this.resultsContainer.style.display = 'block';
        this.results.innerHTML = `
            <div class="search-no-results">
                <i class="bi bi-search"></i>
                <h6>No results found</h6>
                <p>Try adjusting your search terms or filters</p>
            </div>
        `;
    }

    showError() {
        this.resultsContainer.style.display = 'block';
        this.results.innerHTML = `
            <div class="search-no-results">
                <i class="bi bi-exclamation-triangle text-danger"></i>
                <h6>Search Error</h6>
                <p>An error occurred while searching. Please try again.</p>
            </div>
        `;
    }

    hideResults() {
        this.resultsContainer.style.display = 'none';
    }

    highlightMatch(text, query) {
        if (!query) return this.escapeHtml(text);

        const regex = new RegExp(`(${this.escapeRegExp(query)})`, 'gi');
        return this.escapeHtml(text).replace(regex, '<mark>$1</mark>');
    }

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    escapeRegExp(string) {
        return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString() + ' ' + date.toLocaleTimeString();
    }
}

// Initialize advanced search
let advancedSearch;
document.addEventListener('DOMContentLoaded', function() {
    advancedSearch = new AdvancedSearchManager();
});

// Make available globally
window.advancedSearch = advancedSearch;
</script>