<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class Handler extends ExceptionHandler
{
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
            //
        });

        $this->renderable(function (Throwable $e, Request $request) {
            $isTokenMismatch = $e instanceof TokenMismatchException;
            $isHttp419 = $e instanceof HttpExceptionInterface && $e->getStatusCode() === 419;

            if (!($isTokenMismatch || $isHttp419)) {
                return null;
            }

            $message = 'Your session has expired. Please login again.';

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'redirect_to' => route('login', [
                        'expired' => 1,
                        'intended' => $request->fullUrl(),
                    ]),
                ], 419);
            }

            $intendedUrl = $request->isMethod('GET')
                ? $request->fullUrl()
                : url()->previous();

            if ($intendedUrl) {
                $request->session()->put('url.intended', $intendedUrl);
            }

            return redirect()
                ->route('login', ['expired' => 1])
                ->with('error', $message);
        });
    }
}
