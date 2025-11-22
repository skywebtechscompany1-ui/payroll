<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Rap2hpoutre\FastExcel\FastExcel;

class DataExportService
{
    /**
     * Export data to CSV format
     */
    public function exportToCSV(string $modelClass, array $filters = [], array $fields = null): string
    {
        $data = $this->prepareExportData($modelClass, $filters, $fields);

        $filename = $this->generateFilename('csv', $modelClass);
        $filepath = storage_path("app/exports/{$filename}");

        // Create CSV file
        $handle = fopen($filepath, 'w');

        // Write header
        if (!empty($data)) {
            fputcsv($handle, array_keys($data[0]));
        }

        // Write data
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }

        fclose($handle);

        return $filepath;
    }

    /**
     * Export data to Excel format
     */
    public function exportToExcel(string $modelClass, array $filters = [], array $fields = null): string
    {
        $data = $this->prepareExportData($modelClass, $filters, $fields);
        $filename = $this->generateFilename('xlsx', $modelClass);
        $filepath = storage_path("app/exports/{$filename}");

        // Create directory if it doesn't exist
        $directory = dirname($filepath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Convert to collection for FastExcel
        $collection = collect($data);

        // Export to Excel
        (new FastExcel($collection))->export($filepath);

        return $filepath;
    }

    /**
     * Export data to JSON format
     */
    public function exportToJSON(string $modelClass, array $filters = [], array $fields = null): string
    {
        $data = $this->prepareExportData($modelClass, $filters, $fields);
        $filename = $this->generateFilename('json', $modelClass);
        $filepath = storage_path("app/exports/{$filename}");

        // Create directory if it doesn't exist
        $directory = dirname($filepath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        file_put_contents($filepath, json_encode([
            'metadata' => [
                'exported_at' => now()->toISOString(),
                'model' => $modelClass,
                'count' => count($data),
                'filters' => $filters,
                'format' => 'json'
            ],
            'data' => $data
        ], JSON_PRETTY_PRINT));

        return $filepath;
    }

    /**
     * Export data to PDF format
     */
    public function exportToPDF(string $modelClass, array $filters = [], array $fields = null): string
    {
        $data = $this->prepareExportData($modelClass, $filters, $fields);
        $filename = $this->generateFilename('pdf', $modelClass);
        $filepath = storage_path("app/exports/{$filename}");

        // Create directory if it doesn't exist
        $directory = dirname($filepath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        // Generate PDF content
        $html = $this->generatePDFHTML($data, $modelClass, $filters);

        // Use DOMPDF or similar library to generate PDF
        // For now, we'll save as HTML (can be enhanced with actual PDF library)
        file_put_contents($filepath, $html);

        return $filepath;
    }

    /**
     * Export payroll reports
     */
    public function exportPayrollReport(string $type, array $params = [], string $format = 'xlsx'): string
    {
        switch ($type) {
            case 'monthly_summary':
                return $this->exportMonthlyPayrollSummary($params, $format);
            case 'department_wise':
                return $this->exportDepartmentWisePayroll($params, $format);
            case 'tax_summary':
                return $this->exportTaxSummary($params, $format);
            case 'leave_summary':
                return $this->exportLeaveSummary($params, $format);
            case 'attendance_summary':
                return $this->exportAttendanceSummary($params, $format);
            default:
                throw new \InvalidArgumentException("Unknown payroll report type: {$type}");
        }
    }

    /**
     * Prepare export data from model
     */
    protected function prepareExportData(string $modelClass, array $filters, array $fields = null): array
    {
        /** @var Model $model */
        $model = new $modelClass();
        $query = $model->newQuery();

        // Apply filters
        $this->applyExportFilters($query, $filters);

        // Apply permissions
        $this->applyExportPermissions($query, $modelClass);

        // Get data
        $data = $query->get();

        // Transform data
        return $data->map(function ($item) use ($fields) {
            return $this->transformForExport($item, $fields);
        })->toArray();
    }

    /**
     * Apply export filters
     */
    protected function applyExportFilters($query, array $filters): void
    {
        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                if (isset($value['start']) && isset($value['end'])) {
                    $query->whereBetween($field, [$value['start'], $value['end']]);
                } else {
                    $query->whereIn($field, $value);
                }
            } elseif (str_contains($value, '*')) {
                $query->where($field, 'LIKE', str_replace('*', '%', $value));
            } else {
                $query->where($field, $value);
            }
        }
    }

    /**
     * Apply export permissions
     */
    protected function applyExportPermissions($query, string $modelClass): void
    {
        $user = auth()->user();

        if (!$user) {
            throw new \UnauthorizedException('Authentication required for data export');
        }

        // Admin can export everything
        if ($user->hasRole('admin')) {
            return;
        }

        // Apply model-specific restrictions
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

            case \App\Models\Leave::class:
                if (!$user->hasRole(['hr', 'manager'])) {
                    $query->where('user_id', $user->id);
                }
                break;
        }
    }

    /**
     * Transform model data for export
     */
    protected function transformForExport(Model $item, array $fields = null): array
    {
        $data = [];

        if ($fields === null) {
            // Use default export fields
            $fields = $this->getDefaultExportFields($item);
        }

        foreach ($fields as $field) {
            $data[$field] = $this->getFieldValueForExport($item, $field);
        }

        return $data;
    }

    /**
     * Get field value for export with formatting
     */
    protected function getFieldValueForExport(Model $item, string $field): mixed
    {
        if (str_contains($field, '.')) {
            return $this->getRelationValueForExport($item, $field);
        }

        $value = $item->{$field};

        // Format dates
        if ($value instanceof \Carbon\Carbon) {
            return $value->format('Y-m-d H:i:s');
        }

        // Format currency
        if (in_array($field, ['salary', 'net_salary', 'gross_salary', 'overtime_pay']) && is_numeric($value)) {
            return number_format($value, 2);
        }

        // Format booleans
        if (is_bool($value)) {
            return $value ? 'Yes' : 'No';
        }

        return $value;
    }

    /**
     * Get relation value for export
     */
    protected function getRelationValueForExport(Model $item, string $field): mixed
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

        return $this->getFieldValueForExport((object) ['field' => $value], 'field');
    }

    /**
     * Get default export fields for a model
     */
    protected function getDefaultExportFields(Model $item): array
    {
        $modelClass = get_class($item);

        $fieldMappings = [
            \App\Models\User::class => ['id', 'name', 'email', 'department.name', 'position', 'created_at'],
            \App\Models\Employee::class => ['id', 'employee_number', 'first_name', 'last_name', 'email', 'phone', 'department.name', 'position', 'salary', 'hire_date', 'status'],
            \App\Models\Payroll::class => ['id', 'user.name', 'period', 'basic_salary', 'allowances', 'deductions', 'net_salary', 'status', 'pay_date'],
            \App\Models\Leave::class => ['id', 'user.name', 'leave_type', 'start_date', 'end_date', 'reason', 'status', 'approved_by', 'created_at'],
            \App\Models\Attendance::class => ['id', 'user.name', 'date', 'check_in', 'check_out', 'break_duration', 'work_hours', 'status', 'overtime_hours'],
        ];

        return $fieldMappings[$modelClass] ?? array_keys($item->getAttributes());
    }

    /**
     * Generate filename for export
     */
    protected function generateFilename(string $format, string $modelClass): string
    {
        $modelName = strtolower(class_basename($modelClass));
        $timestamp = Carbon::now()->format('Y_m_d_His');

        return "{$modelName}_export_{$timestamp}.{$format}";
    }

    /**
     * Generate HTML for PDF export
     */
    protected function generatePDFHTML(array $data, string $modelClass, array $filters): string
    {
        $modelName = class_basename($modelClass);
        $title = "{$modelName} Export Report";

        $html = "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>{$title}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #333; }
        .header .date { color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .summary { margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>{$title}</h1>
        <p class='date'>Generated on: " . now()->format('M j, Y H:i:s') . "</p>
        <p>Total Records: " . count($data) . "</p>
    </div>";

        if (!empty($filters)) {
            $html .= "<div class='summary'>
                <h3>Applied Filters:</h3>
                <ul>";
                foreach ($filters as $field => $value) {
                    $html .= "<li><strong>{$field}:</strong> " . (is_array($value) ? implode(', ', $value) : $value) . "</li>";
                }
                $html .= "</ul>
            </div>";
        }

        if (!empty($data)) {
            $html .= "<table>
                <thead>
                    <tr>";
                    foreach (array_keys($data[0]) as $column) {
                        $html .= "<th>" . ucfirst(str_replace('_', ' ', $column)) . "</th>";
                    }
                    $html .= "</tr>
                </thead>
                <tbody>";

                foreach ($data as $row) {
                    $html .= "<tr>";
                    foreach ($row as $cell) {
                        $html .= "<td>" . htmlspecialchars($cell) . "</td>";
                    }
                    $html .= "</tr>";
                }

                $html .= "</tbody>
            </table>";
        } else {
            $html .= "<p>No data found.</p>";
        }

        $html .= "</body></html>";

        return $html;
    }

    /**
     * Export monthly payroll summary
     */
    protected function exportMonthlyPayrollSummary(array $params, string $format): string
    {
        $month = $params['month'] ?? now()->month;
        $year = $params['year'] ?? now()->year;

        $data = DB::table('payrolls as p')
            ->join('users as u', 'p.user_id', '=', 'u.id')
            ->join('employees as e', 'p.user_id', '=', 'e.user_id')
            ->join('departments as d', 'e.department_id', '=', 'd.id')
            ->whereMonth('p.pay_date', $month)
            ->whereYear('p.pay_date', $year)
            ->where('p.status', 'processed')
            ->select([
                'u.name as employee_name',
                'e.employee_number',
                'd.name as department',
                'p.basic_salary',
                'p.allowances',
                'p.deductions',
                'p.net_salary',
                'p.pay_date'
            ])
            ->orderBy('d.name')
            ->orderBy('u.name')
            ->get();

        $filename = "monthly_payroll_summary_{$year}_{$month}.{$format}";
        $filepath = storage_path("app/exports/{$filename}");

        return $this->saveExportData($data->toArray(), $filepath, $format, [
            'type' => 'monthly_payroll_summary',
            'period' => "{$year}-{$month}",
            'count' => $data->count()
        ]);
    }

    /**
     * Export department-wise payroll
     */
    protected function exportDepartmentWisePayroll(array $params, string $format): string
    {
        $month = $params['month'] ?? now()->month;
        $year = $params['year'] ?? now()->year;

        $data = DB::table('payrolls as p')
            ->join('users as u', 'p.user_id', '=', 'u.id')
            ->join('employees as e', 'p.user_id', '=', 'e.user_id')
            ->join('departments as d', 'e.department_id', '=', 'd.id')
            ->whereMonth('p.pay_date', $month)
            ->whereYear('p.pay_date', $year)
            ->where('p.status', 'processed')
            ->select([
                'd.name as department',
                DB::raw('COUNT(*) as employee_count'),
                DB::raw('SUM(p.basic_salary) as total_basic'),
                DB::raw('SUM(p.allowances) as total_allowances'),
                DB::raw('SUM(p.deductions) as total_deductions'),
                DB::raw('SUM(p.net_salary) as total_net'),
                DB::raw('AVG(p.net_salary) as average_net')
            ])
            ->groupBy('d.id', 'd.name')
            ->orderBy('d.name')
            ->get();

        $filename = "department_wise_payroll_{$year}_{$month}.{$format}";
        $filepath = storage_path("app/exports/{$filename}");

        return $this->saveExportData($data->toArray(), $filepath, $format, [
            'type' => 'department_wise_payroll',
            'period' => "{$year}-{$month}",
            'departments' => $data->count()
        ]);
    }

    /**
     * Export tax summary
     */
    protected function exportTaxSummary(array $params, string $format): string
    {
        $year = $params['year'] ?? now()->year;

        $data = DB::table('payrolls as p')
            ->join('users as u', 'p.user_id', '=', 'u.id')
            ->join('employees as e', 'p.user_id', '=', 'e.user_id')
            ->whereYear('p.pay_date', $year)
            ->where('p.status', 'processed')
            ->select([
                'u.name as employee_name',
                'e.employee_number',
                DB::raw('SUM(p.basic_salary) as annual_basic'),
                DB::raw('SUM(p.allowances) as annual_allowances'),
                DB::raw('SUM(p.deductions) as annual_deductions'),
                DB::raw('SUM(p.net_salary) as annual_net'),
                DB::raw('SUM(p.tax) as annual_tax')
            ])
            ->groupBy('p.user_id', 'u.name', 'e.employee_number')
            ->orderBy('u.name')
            ->get();

        $filename = "tax_summary_{$year}.{$format}";
        $filepath = storage_path("app/exports/{$filename}");

        return $this->saveExportData($data->toArray(), $filepath, $format, [
            'type' => 'tax_summary',
            'year' => $year,
            'employees' => $data->count()
        ]);
    }

    /**
     * Export leave summary
     */
    protected function exportLeaveSummary(array $params, string $format): string
    {
        $year = $params['year'] ?? now()->year;

        $data = DB::table('leaves as l')
            ->join('users as u', 'l.user_id', '=', 'u.id')
            ->join('employees as e', 'l.user_id', '=', 'e.user_id')
            ->join('departments as d', 'e.department_id', '=', 'd.id')
            ->whereYear('l.start_date', $year)
            ->select([
                'd.name as department',
                'l.leave_type',
                'l.status',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(DATEDIFF(l.end_date, l.start_date) + 1) as total_days')
            ])
            ->groupBy('d.id', 'd.name', 'l.leave_type', 'l.status')
            ->orderBy('d.name')
            ->orderBy('l.leave_type')
            ->get();

        $filename = "leave_summary_{$year}.{$format}";
        $filepath = storage_path("app/exports/{$filename}");

        return $this->saveExportData($data->toArray(), $filepath, $format, [
            'type' => 'leave_summary',
            'year' => $year,
            'records' => $data->count()
        ]);
    }

    /**
     * Export attendance summary
     */
    protected function exportAttendanceSummary(array $params, string $format): string
    {
        $month = $params['month'] ?? now()->month;
        $year = $params['year'] ?? now()->year;

        $data = DB::table('attendances as a')
            ->join('users as u', 'a.user_id', '=', 'u.id')
            ->join('employees as e', 'a.user_id', '=', 'e.user_id')
            ->join('departments as d', 'e.department_id', '=', 'd.id')
            ->whereMonth('a.date', $month)
            ->whereYear('a.date', $year)
            ->select([
                'u.name as employee_name',
                'e.employee_number',
                'd.name as department',
                DB::raw('COUNT(*) as total_days'),
                DB::raw('SUM(CASE WHEN a.status = "present" THEN 1 ELSE 0 END) as present_days'),
                DB::raw('SUM(CASE WHEN a.status = "absent" THEN 1 ELSE 0 END) as absent_days'),
                DB::raw('SUM(CASE WHEN a.status = "late" THEN 1 ELSE 0 END) as late_days'),
                DB::raw('SUM(a.overtime_hours) as total_overtime'),
                DB::raw('SUM(a.work_hours) as total_work_hours')
            ])
            ->groupBy('a.user_id', 'u.name', 'e.employee_number', 'd.name')
            ->orderBy('d.name')
            ->orderBy('u.name')
            ->get();

        $filename = "attendance_summary_{$year}_{$month}.{$format}";
        $filepath = storage_path("app/exports/{$filename}");

        return $this->saveExportData($data->toArray(), $filepath, $format, [
            'type' => 'attendance_summary',
            'period' => "{$year}-{$month}",
            'employees' => $data->count()
        ]);
    }

    /**
     * Save data to export file
     */
    protected function saveExportData(array $data, string $filepath, string $format, array $metadata = []): string
    {
        $directory = dirname($filepath);
        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        switch ($format) {
            case 'csv':
                return $this->saveAsCSV($data, $filepath);
            case 'xlsx':
                return $this->saveAsExcel($data, $filepath);
            case 'json':
                return $this->saveAsJSON($data, $filepath, $metadata);
            case 'pdf':
                return $this->saveAsPDF($data, $filepath, $metadata);
            default:
                throw new \InvalidArgumentException("Unsupported export format: {$format}");
        }
    }

    /**
     * Save as CSV
     */
    protected function saveAsCSV(array $data, string $filepath): string
    {
        $handle = fopen($filepath, 'w');

        if (!empty($data)) {
            fputcsv($handle, array_keys($data[0]));

            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
        }

        fclose($handle);
        return $filepath;
    }

    /**
     * Save as Excel
     */
    protected function saveAsExcel(array $data, string $filepath): string
    {
        $collection = collect($data);
        (new FastExcel($collection))->export($filepath);
        return $filepath;
    }

    /**
     * Save as JSON
     */
    protected function saveAsJSON(array $data, string $filepath, array $metadata): string
    {
        $exportData = [
            'metadata' => array_merge([
                'exported_at' => now()->toISOString(),
                'count' => count($data),
                'format' => 'json'
            ], $metadata),
            'data' => $data
        ];

        file_put_contents($filepath, json_encode($exportData, JSON_PRETTY_PRINT));
        return $filepath;
    }

    /**
     * Save as PDF
     */
    protected function saveAsPDF(array $data, string $filepath, array $metadata): string
    {
        $html = $this->generateReportHTML($data, $metadata);
        file_put_contents($filepath, $html);
        return $filepath;
    }

    /**
     * Generate report HTML for PDF
     */
    protected function generateReportHTML(array $data, array $metadata): string
    {
        $title = $metadata['type'] ?? 'Data Export Report';

        return "<!DOCTYPE html>
<html>
<head>
    <meta charset='utf-8'>
    <title>{$title}</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; font-weight: bold; }
        tr:nth-child(even) { background-color: #f9f9f9; }
    </style>
</head>
<body>
    <div class='header'>
        <h1>" . ucfirst(str_replace('_', ' ', $title)) . "</h1>
        <p>Generated on: " . now()->format('M j, Y H:i:s') . "</p>
        <p>Total Records: " . count($data) . "</p>
    </div>
    <table>
        <thead>
            <tr>";
            if (!empty($data)) {
                foreach (array_keys($data[0]) as $column) {
                    echo "<th>" . ucfirst(str_replace('_', ' ', $column)) . "</th>";
                }
            }
            echo "</tr>
        </thead>
        <tbody>";

        foreach ($data as $row) {
            echo "<tr>";
            foreach ($row as $cell) {
                echo "<td>" . htmlspecialchars($cell) . "</td>";
            }
            echo "</tr>";
        }

        echo "</tbody>
    </table>
</body>
</html>";
    }

    /**
     * Clean up old export files
     */
    public function cleanupOldExports(int $daysToKeep = 7): int
    {
        $exportPath = storage_path('app/exports');
        $cutoff = now()->subDays($daysToKeep);
        $deleted = 0;

        if (is_dir($exportPath)) {
            $files = glob($exportPath . '/*');

            foreach ($files as $file) {
                if (is_file($file) && filemtime($file) < $cutoff->timestamp) {
                    unlink($file);
                    $deleted++;
                }
            }
        }

        return $deleted;
    }

    /**
     * Get export statistics
     */
    public function getExportStatistics(): array
    {
        $exportPath = storage_path('app/exports');

        if (!is_dir($exportPath)) {
            return [
                'total_files' => 0,
                'total_size' => 0,
                'by_format' => [],
                'recent_exports' => []
            ];
        }

        $files = glob($exportPath . '/*');
        $totalFiles = count($files);
        $totalSize = 0;
        $byFormat = [];
        $recentExports = [];

        foreach ($files as $file) {
            if (is_file($file)) {
                $size = filesize($file);
                $totalSize += $size;

                $extension = pathinfo($file, PATHINFO_EXTENSION);
                $byFormat[$extension] = ($byFormat[$extension] ?? 0) + 1;

                $filename = basename($file);
                $modified = filemtime($file);

                $recentExports[] = [
                    'filename' => $filename,
                    'size' => $size,
                    'modified' => $modified,
                    'format' => $extension
                ];
            }
        }

        // Sort recent exports by modification time
        usort($recentExports, function ($a, $b) {
            return $b['modified'] <=> $a['modified'];
        });

        // Take only 10 most recent
        $recentExports = array_slice($recentExports, 0, 10);

        return [
            'total_files' => $totalFiles,
            'total_size' => $totalSize,
            'total_size_human' => $this->formatBytes($totalSize),
            'by_format' => $byFormat,
            'recent_exports' => $recentExports
        ];
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }
}