<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SearchService
{
    /**
     * Perform a global search across multiple models
     */
    public function globalSearch(string $query, array $models = null, int $limit = 10): array
    {
        if (empty(trim($query))) {
            return [];
        }

        $models = $models ?? $this->getDefaultSearchableModels();
        $results = [];

        foreach ($models as $modelClass => $config) {
            try {
                $modelResults = $this->searchModel($query, $modelClass, $config, $limit);
                if ($modelResults->isNotEmpty()) {
                    $results[$config['name']] = $modelResults;
                }
            } catch (\Exception $e) {
                \Log::error("Search failed for model {$modelClass}: " . $e->getMessage());
            }
        }

        return $results;
    }

    /**
     * Search a specific model
     */
    public function searchModel(string $query, string $modelClass, array $config, int $limit = 10): Collection
    {
        /** @var Model $model */
        $model = new $modelClass();
        $queryBuilder = $model->newQuery();

        // Apply basic text search on searchable fields
        $searchFields = $config['fields'] ?? [];
        if (!empty($searchFields)) {
            $queryBuilder->where(function (Builder $q) use ($query, $searchFields) {
                foreach ($searchFields as $field) {
                    if (str_contains($field, '.')) {
                        // Handle relationship fields
                        $this->addRelationshipSearch($q, $field, $query);
                    } else {
                        $q->orWhere($field, 'LIKE', "%{$query}%");
                    }
                }
            });
        }

        // Apply filters
        if (isset($config['filters'])) {
            $this->applyFilters($queryBuilder, $config['filters'], $query);
        }

        // Apply date range filters
        if (isset($config['date_fields'])) {
            $this->applyDateFilters($queryBuilder, $config['date_fields'], $query);
        }

        // Apply custom scope
        if (isset($config['scope'])) {
            $queryBuilder->{$config['scope']}();
        }

        // Apply permissions/scoping
        $this->applyPermissions($queryBuilder, $modelClass);

        // Get results with formatted data
        return $queryBuilder->limit($limit)
            ->get()
            ->map(function ($item) use ($config) {
                return $this->formatSearchResult($item, $config);
            });
    }

    /**
     * Add relationship search
     */
    protected function addRelationshipSearch(Builder $query, string $field, string $searchQuery): void
    {
        $relations = explode('.', $field);
        $field = array_pop($relations);
        $relation = implode('.', $relations);

        $query->orWhereHas($relation, function (Builder $q) use ($field, $searchQuery) {
            $q->where($field, 'LIKE', "%{$searchQuery}%");
        });
    }

    /**
     * Apply filters to search query
     */
    protected function applyFilters(Builder $query, array $filters, string $searchQuery): void
    {
        foreach ($filters as $filter) {
            switch ($filter['type']) {
                case 'exact':
                    if (str_contains($searchQuery, $filter['prefix'] ?? '')) {
                        $value = str_replace($filter['prefix'] ?? '', '', $searchQuery);
                        $query->where($filter['field'], $value);
                    }
                    break;

                case 'range':
                    if (preg_match($filter['pattern'], $searchQuery, $matches)) {
                        $min = $matches[1] ?? null;
                        $max = $matches[2] ?? null;
                        if ($min !== null) {
                            $query->where($filter['field'], '>=', $min);
                        }
                        if ($max !== null) {
                            $query->where($filter['field'], '<=', $max);
                        }
                    }
                    break;

                case 'boolean':
                    $keywords = $filter['keywords'] ?? [];
                    foreach ($keywords as $keyword => $value) {
                        if (str_contains(strtolower($searchQuery), $keyword)) {
                            $query->where($filter['field'], $value);
                        }
                    }
                    break;
            }
        }
    }

    /**
     * Apply date range filters
     */
    protected function applyDateFilters(Builder $query, array $dateFields, string $searchQuery): void
    {
        foreach ($dateFields as $field => $patterns) {
            foreach ($patterns as $pattern => $modifier) {
                if (str_contains(strtolower($searchQuery), $pattern)) {
                    $date = now()->modify($modifier);
                    $query->where($field, '>=', $date->startOfDay())
                          ->where($field, '<=', $date->endOfDay());
                }
            }
        }
    }

    /**
     * Apply permission-based filtering
     */
    protected function applyPermissions(Builder $query, string $modelClass): void
    {
        $user = auth()->user();

        if (!$user) {
            // Only show public data for unauthenticated users
            $query->where('is_public', true);
            return;
        }

        // Admin can see everything
        if ($user->hasRole('admin')) {
            return;
        }

        // Apply model-specific permissions
        switch ($modelClass) {
            case \App\Models\User::class:
                if (!$user->hasRole('hr')) {
                    $query->where('id', $user->id)
                          ->orWhere('department_id', $user->department_id);
                }
                break;

            case \App\Models\Employee::class:
                if (!$user->hasRole(['hr', 'payroll_admin'])) {
                    $query->where('user_id', $user->id)
                          ->orWhere('department_id', $user->department_id);
                }
                break;

            case \App\Models\Payroll::class:
                if (!$user->hasRole(['hr', 'payroll_admin'])) {
                    $query->where('user_id', $user->id);
                }
                break;
        }
    }

    /**
     * Format search result for display
     */
    protected function formatSearchResult(Model $item, array $config): array
    {
        $result = [
            'id' => $item->getKey(),
            'title' => $this->getSearchTitle($item, $config),
            'description' => $this->getSearchDescription($item, $config),
            'url' => $this->getSearchUrl($item, $config),
            'type' => $config['name'],
            'icon' => $config['icon'] ?? 'bi-file-text',
            'highlight' => $this->getHighlightedFields($item, $config),
        ];

        // Add additional data
        if (isset($config['additional_data'])) {
            foreach ($config['additional_data'] as $key => $field) {
                $result[$key] = $this->getFieldValue($item, $field);
            }
        }

        return $result;
    }

    /**
     * Get search title for an item
     */
    protected function getSearchTitle(Model $item, array $config): string
    {
        $titleField = $config['title_field'] ?? 'name';

        if (str_contains($titleField, '.')) {
            return $this->getRelationValue($item, $titleField);
        }

        return $item->{$titleField} ?? 'Untitled';
    }

    /**
     * Get search description for an item
     */
    protected function getSearchDescription(Model $item, array $config): string
    {
        $descField = $config['description_field'] ?? null;

        if (!$descField) {
            return '';
        }

        if (str_contains($descField, '.')) {
            return $this->getRelationValue($item, $descField);
        }

        $value = $item->{$descField};
        return $value ? Str::limit(strip_tags($value), 150) : '';
    }

    /**
     * Get search URL for an item
     */
    protected function getSearchUrl(Model $item, array $config): string
    {
        $urlPattern = $config['url_pattern'] ?? null;

        if ($urlPattern) {
            return str_replace('{id}', $item->getKey(), $urlPattern);
        }

        // Fallback to standard resource URL
        $modelName = strtolower(class_basename($item));
        return "/{$modelName}/{$item->getKey()}";
    }

    /**
     * Get highlighted search fields
     */
    protected function getHighlightedFields(Model $item, array $config): array
    {
        $highlightFields = $config['highlight_fields'] ?? [];
        $highlighted = [];

        foreach ($highlightFields as $field => $label) {
            $value = $this->getFieldValue($item, $field);
            if ($value) {
                $highlighted[$label] = $value;
            }
        }

        return $highlighted;
    }

    /**
     * Get field value from model (supports relationships)
     */
    protected function getFieldValue(Model $item, string $field): mixed
    {
        if (str_contains($field, '.')) {
            return $this->getRelationValue($item, $field);
        }

        return $item->{$field};
    }

    /**
     * Get relationship value
     */
    protected function getRelationValue(Model $item, string $field): mixed
    {
        $relations = explode('.', $field);
        $value = $item;

        foreach ($relations as $relation) {
            if ($value && isset($value->{$relation})) {
                $value = $value->{$relation};
            } else {
                return null;
            }
        }

        return $value;
    }

    /**
     * Get default searchable models
     */
    protected function getDefaultSearchableModels(): array
    {
        return [
            \App\Models\User::class => [
                'name' => 'users',
                'icon' => 'bi-people',
                'title_field' => 'name',
                'description_field' => 'email',
                'url_pattern' => '/users/{id}',
                'fields' => ['name', 'email', 'employee.number'],
                'highlight_fields' => [
                    'name' => 'Full Name',
                    'email' => 'Email',
                    'employee.number' => 'Employee Number'
                ],
                'filters' => [
                    [
                        'type' => 'exact',
                        'field' => 'email',
                        'prefix' => 'email:'
                    ]
                ]
            ],
            \App\Models\Employee::class => [
                'name' => 'employees',
                'icon' => 'bi-person-badge',
                'title_field' => 'full_name',
                'description_field' => 'position',
                'url_pattern' => '/employees/{id}',
                'fields' => ['first_name', 'last_name', 'employee_number', 'position', 'department.name'],
                'highlight_fields' => [
                    'full_name' => 'Full Name',
                    'employee_number' => 'Employee Number',
                    'position' => 'Position',
                    'department.name' => 'Department'
                ],
                'date_fields' => [
                    'hire_date' => [
                        'hired this week' => '-1 week',
                        'hired this month' => '-1 month'
                    ]
                ]
            ],
            \App\Models\Payroll::class => [
                'name' => 'payroll',
                'icon' => 'bi-cash-stack',
                'title_field' => 'user.name',
                'description_field' => 'net_salary',
                'url_pattern' => '/payroll/{id}',
                'fields' => ['user.name', 'user.email', 'period', 'status'],
                'highlight_fields' => [
                    'user.name' => 'Employee',
                    'period' => 'Period',
                    'net_salary' => 'Net Salary',
                    'status' => 'Status'
                ],
                'filters' => [
                    [
                        'type' => 'range',
                        'field' => 'net_salary',
                        'pattern' => '/salary\s*(\d+)-(\d+)/'
                    ],
                    [
                        'type' => 'boolean',
                        'field' => 'status',
                        'keywords' => ['pending' => 'pending', 'processed' => 'processed']
                    ]
                ],
                'date_fields' => [
                    'pay_date' => [
                        'paid this week' => '-1 week',
                        'paid this month' => '-1 month'
                    ]
                ]
            ],
            \App\Models\Leave::class => [
                'name' => 'leaves',
                'icon' => 'bi-calendar-x',
                'title_field' => 'user.name',
                'description_field' => 'leave_type',
                'url_pattern' => '/leaves/{id}',
                'fields' => ['user.name', 'leave_type', 'status', 'start_date', 'end_date'],
                'highlight_fields' => [
                    'user.name' => 'Employee',
                    'leave_type' => 'Leave Type',
                    'status' => 'Status'
                ],
                'filters' => [
                    [
                        'type' => 'boolean',
                        'field' => 'status',
                        'keywords' => ['approved' => 'approved', 'pending' => 'pending', 'rejected' => 'rejected']
                    ]
                ]
            ],
            \App\Models\Attendance::class => [
                'name' => 'attendance',
                'icon' => 'bi-clock-history',
                'title_field' => 'user.name',
                'description_field' => 'date',
                'url_pattern' => '/attendance/{id}',
                'fields' => ['user.name', 'date', 'check_in', 'check_out', 'status'],
                'highlight_fields' => [
                    'user.name' => 'Employee',
                    'date' => 'Date',
                    'check_in' => 'Check In',
                    'check_out' => 'Check Out'
                ],
                'date_fields' => [
                    'date' => [
                        'today' => 'today',
                        'this week' => '-1 week',
                        'this month' => '-1 month'
                    ]
                ]
            ]
        ];
    }

    /**
     * Get search suggestions
     */
    public function getSuggestions(string $query, array $models = null): array
    {
        if (strlen($query) < 2) {
            return [];
        }

        $suggestions = [];
        $models = $models ?? array_keys($this->getDefaultSearchableModels());

        foreach ($models as $modelClass) {
            try {
                $model = new $modelClass();
                $searchFields = $this->getModelSearchFields($modelClass);

                foreach ($searchFields as $field) {
                    $suggestions = array_merge(
                        $suggestions,
                        $this->getFieldSuggestions($model, $field, $query)
                    );
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Remove duplicates and limit
        return array_unique(array_slice($suggestions, 0, 10));
    }

    /**
     * Get search fields for a model
     */
    protected function getModelSearchFields(string $modelClass): array
    {
        $config = $this->getDefaultSearchableModels()[$modelClass] ?? [];
        return $config['fields'] ?? [];
    }

    /**
     * Get field suggestions for autocomplete
     */
    protected function getFieldSuggestions(Model $model, string $field, string $query): array
    {
        if (str_contains($field, '.')) {
            return $this->getRelationshipSuggestions($model, $field, $query);
        }

        return $model->newQuery()
            ->where($field, 'LIKE', "{$query}%")
            ->distinct()
            ->pluck($field)
            ->take(5)
            ->filter()
            ->values()
            ->toArray();
    }

    /**
     * Get relationship field suggestions
     */
    protected function getRelationshipSuggestions(Model $model, string $field, string $query): array
    {
        $relations = explode('.', $field);
        $searchField = array_pop($relations);
        $relation = implode('.', $relations);

        return $model->newQuery()
            ->whereHas($relation, function (Builder $q) use ($searchField, $query) {
                $q->where($searchField, 'LIKE', "{$query}%");
            })
            ->with($relation)
            ->get()
            ->map(function ($item) use ($relation, $searchField) {
                $related = $this->getRelationValue($item, $relation);
                return $related->{$searchField} ?? null;
            })
            ->filter()
            ->unique()
            ->take(5)
            ->values()
            ->toArray();
    }

    /**
     * Save search to user history
     */
    public function saveSearchHistory(string $query, array $results = []): void
    {
        if (!auth()->check() || empty(trim($query))) {
            return;
        }

        DB::table('search_history')->insert([
            'user_id' => auth()->id(),
            'query' => $query,
            'results_count' => array_sum(array_map('count', $results)),
            'created_at' => now(),
        ]);

        // Clean up old history (keep last 100 searches per user)
        DB::table('search_history')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->offset(100)
            ->delete();
    }

    /**
     * Get user search history
     */
    public function getSearchHistory(int $limit = 10): Collection
    {
        if (!auth()->check()) {
            return collect();
        }

        return DB::table('search_history')
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->pluck('query');
    }

    /**
     * Get popular searches
     */
    public function getPopularSearches(int $limit = 10): Collection
    {
        return DB::table('search_history')
            ->select('query', DB::raw('COUNT(*) as count'), DB::raw('MAX(created_at) as last_searched'))
            ->groupBy('query')
            ->orderBy('count', 'desc')
            ->orderBy('last_searched', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get search analytics
     */
    public function getSearchAnalytics(): array
    {
        return [
            'total_searches' => DB::table('search_history')->count(),
            'unique_searches' => DB::table('search_history')->distinct('query')->count('query'),
            'searches_today' => DB::table('search_history')
                ->whereDate('created_at', today())
                ->count(),
            'popular_searches' => $this->getPopularSearches(5),
            'recent_searches' => auth()->check() ? $this->getSearchHistory(5) : collect(),
        ];
    }
}