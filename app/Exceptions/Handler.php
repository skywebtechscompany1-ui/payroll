<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;
use App\Mail\ExceptionOccurred;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        AuthenticationException::class => 'warning',
        AuthorizationException::class => 'warning',
        ValidationException::class => 'info',
        ModelNotFoundException::class => 'warning',
        NotFoundHttpException::class => 'warning',
        MethodNotAllowedHttpException::class => 'warning',
        AccessDeniedHttpException::class => 'warning',
        TokenMismatchException::class => 'warning',
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        ValidationException::class,
        TokenMismatchException::class,
    ];

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            $this->logException($e);
            $this->notifyAdmins($e);
            $this->storeErrorInDatabase($e);
        });

        $this->renderable(function (Throwable $e, Request $request) {
            return $this->handleApiException($request, $e);
        });
    }

    /**
     * Report or log an exception.
     */
    public function report(Throwable $e): void
    {
        if ($this->shouldntReport($e)) {
            return;
        }

        parent::report($e);
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Log the exception
        $this->logException($e);

        // Handle different request types
        if ($request->expectsJson() || $request->is('api/*')) {
            return $this->handleApiException($request, $e);
        }

        return $this->handleWebException($request, $e);
    }

    /**
     * Log exception with context information
     */
    protected function logException(Throwable $e): void
    {
        $context = [
            'url' => request()->fullUrl(),
            'method' => request()->method(),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'timestamp' => now()->toISOString(),
            'exception_class' => get_class($e),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];

        // Add request data if available
        if (request()->has('data')) {
            $context['request_data'] = request()->except(['password', 'password_confirmation', 'current_password']);
        }

        // Determine log level based on exception type
        $level = $this->getLogLevel($e);

        Log::log($level, $e->getMessage(), $context);

        // Also log to separate channels for specific exception types
        $this->logToSpecificChannels($e, $context);
    }

    /**
     * Determine appropriate log level for exception
     */
    protected function getLogLevel(Throwable $e): string
    {
        foreach ($this->levels as $type => $level) {
            if ($e instanceof $type) {
                return $level;
            }
        }

        return 'error'; // Default to error level
    }

    /**
     * Log to specific channels based on exception type
     */
    protected function logToSpecificChannels(Throwable $e, array $context): void
    {
        // Security-related exceptions
        if ($e instanceof AuthenticationException || $e instanceof AuthorizationException) {
            Log::channel('security')->warning('Security-related exception', $context);
        }

        // Database-related exceptions
        if (str_contains($e->getMessage(), 'SQL') || str_contains($e->getMessage(), 'Database')) {
            Log::channel('database')->error('Database exception', $context);
        }

        // API-related exceptions
        if (request()->is('api/*')) {
            Log::channel('api')->error('API exception', $context);
        }

        // Performance-related exceptions
        if (str_contains($e->getMessage(), 'timeout') || str_contains($e->getMessage(), 'memory')) {
            Log::channel('performance')->critical('Performance exception', $context);
        }
    }

    /**
     * Notify administrators of critical exceptions
     */
    protected function notifyAdmins(Throwable $e): void
    {
        // Only notify for critical exceptions
        if (!$this->isCriticalException($e)) {
            return;
        }

        // Don't notify in local environment
        if (app()->environment(['local', 'testing'])) {
            return;
        }

        try {
            $adminEmails = config('app.admin_emails', [config('app.admin_email')]);

            foreach ($adminEmails as $email) {
                if ($email) {
                    Mail::to($email)->queue(new ExceptionOccurred($e));
                }
            }
        } catch (Throwable $mailException) {
            Log::error('Failed to send exception notification email', [
                'original_exception' => $e->getMessage(),
                'mail_exception' => $mailException->getMessage(),
            ]);
        }
    }

    /**
     * Determine if exception is critical enough to notify admins
     */
    protected function isCriticalException(Throwable $e): bool
    {
        // Don't notify for user errors (validation, auth, not found)
        $nonCriticalExceptions = [
            AuthenticationException::class,
            AuthorizationException::class,
            ValidationException::class,
            NotFoundHttpException::class,
            MethodNotAllowedHttpException::class,
            AccessDeniedHttpException::class,
            TokenMismatchException::class,
        ];

        foreach ($nonCriticalExceptions as $type) {
            if ($e instanceof $type) {
                return false;
            }
        }

        return true;
    }

    /**
     * Store error in database for analysis
     */
    protected function storeErrorInDatabase(Throwable $e): void
    {
        try {
            \App\Models\ErrorLog::create([
                'exception_class' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => request()->fullUrl(),
                'method' => request()->method(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'user_id' => auth()->id(),
                'request_data' => json_encode(request()->except(['password', 'password_confirmation', 'current_password'])),
                'status_code' => $this->getExceptionStatusCode($e),
                'severity' => $this->getLogLevel($e),
                'resolved' => false,
            ]);
        } catch (Throwable $dbException) {
            // Don't let logging errors break the application
            Log::error('Failed to store error in database', [
                'original_exception' => $e->getMessage(),
                'db_exception' => $dbException->getMessage(),
            ]);
        }
    }

    /**
     * Get HTTP status code for exception
     */
    protected function getExceptionStatusCode(Throwable $e): int
    {
        if ($e instanceof HttpException) {
            return $e->getStatusCode();
        }

        if ($e instanceof ModelNotFoundException) {
            return 404;
        }

        if ($e instanceof AuthenticationException) {
            return 401;
        }

        if ($e instanceof AuthorizationException) {
            return 403;
        }

        if ($e instanceof ValidationException) {
            return 422;
        }

        return 500; // Default server error
    }

    /**
     * Handle API exceptions
     */
    protected function handleApiException($request, Throwable $e)
    {
        $exception = $this->prepareException($e);
        $statusCode = $this->getExceptionStatusCode($exception);

        $response = [
            'success' => false,
            'message' => $this->getExceptionMessage($exception),
            'code' => $statusCode,
            'timestamp' => now()->toISOString(),
        ];

        // Add debug information in development
        if (config('app.debug')) {
            $response['debug'] = [
                'exception' => get_class($exception),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTrace(),
            ];
        }

        // Add validation errors if present
        if ($exception instanceof ValidationException) {
            $response['errors'] = $exception->errors();
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Handle web exceptions
     */
    protected function handleWebException($request, Throwable $e)
    {
        $exception = $this->prepareException($e);

        if ($exception instanceof AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }

        if ($exception instanceof ValidationException) {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }

        if ($exception instanceof AuthorizationException) {
            return $this->unauthorized($request, $exception);
        }

        return parent::render($request, $exception);
    }

    /**
     * Get user-friendly exception message
     */
    protected function getExceptionMessage(Throwable $e): string
    {
        // Return specific messages for common exceptions
        if ($e instanceof AuthenticationException) {
            return 'Authentication required. Please log in to continue.';
        }

        if ($e instanceof AuthorizationException) {
            return 'You do not have permission to perform this action.';
        }

        if ($e instanceof ValidationException) {
            return 'The given data was invalid. Please check your input.';
        }

        if ($e instanceof ModelNotFoundException) {
            return 'The requested resource was not found.';
        }

        if ($e instanceof NotFoundHttpException) {
            return 'The page you are looking for could not be found.';
        }

        if ($e instanceof TokenMismatchException) {
            return 'Your session has expired. Please refresh the page and try again.';
        }

        // Generic message for other exceptions
        return config('app.debug') ? $e->getMessage() : 'An error occurred. Please try again later.';
    }

    /**
     * Convert an authentication exception into a response.
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated.',
                'code' => 401,
            ], 401);
        }

        return redirect()->guest(route('login'));
    }

    /**
     * Convert an authorization exception into a response.
     */
    protected function unauthorized($request, AuthorizationException $exception)
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized.',
                'code' => 403,
            ], 403);
        }

        abort(403, 'Unauthorized');
    }

    /**
     * Create a response object from the given validation exception.
     */
    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        if ($e->response) {
            return $e->response;
        }

        return response()->json([
            'success' => false,
            'message' => 'The given data was invalid.',
            'errors' => $e->errors(),
            'code' => 422,
        ], 422);
    }
}
