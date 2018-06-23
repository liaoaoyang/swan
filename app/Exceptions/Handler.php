<?php

namespace App\Exceptions;

use Exception;
use Log;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport
        = [
            \Illuminate\Auth\AuthenticationException::class,
            \Illuminate\Auth\Access\AuthorizationException::class,
            \Symfony\Component\HttpKernel\Exception\HttpException::class,
            \Illuminate\Database\Eloquent\ModelNotFoundException::class,
            \Illuminate\Session\TokenMismatchException::class,
            \Illuminate\Validation\ValidationException::class,
        ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        $exceptionTrace = $exception->getTrace();
        $line0 = isset($exceptionTrace[0]['line']) ? $exceptionTrace[0]['line'] : '';
        $file0 = isset($exceptionTrace[0]['file']) ? $exceptionTrace[0]['file'] : '';

        if (!config('app.debug')) {
            Log::error('[' . $exception->getCode() . '] "' . $exception->getMessage() . '" on line ' . $line0 . ' of file ' . $file0);
        } else {
            parent::report($exception);
        }
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Exception $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $exception)
    {
        $exceptionData = [
            'exception' => [
                'desc' => '其他错误',
            ],
        ];

        if ($exception instanceof \EasyWeChat\Core\Exceptions\HttpException) {
            if (strpos($exception->getMessage(), 'require subscribe hint') !== false) {
                Log::error(__METHOD__ . '|EasyWeChat Exception: ' . $exception->getMessage());
                $exceptionData['exception']['desc'] = '未关注公众号';
            }
        }

        if ($request->expectsJson() || preg_match('#^/wechat/swan/\S+\.send$#', $request->getPathInfo())) {
            return response()->json($exceptionData);
        }

        return response()->view('swan/exception', $exceptionData);
    }

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Illuminate\Auth\AuthenticationException $exception
     * @return \Illuminate\Http\Response
     */
    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }

        return redirect()->guest(route('login'));
    }
}
