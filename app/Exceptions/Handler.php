<?php

namespace App\Exceptions;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;

use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */

    public function render($request, Throwable $exception)
    {

        if ($request->ajax() || $request->wantsJson() || $request->expectsJson() || $this->isApiRoute($request)) {
            return response()->json([
                'error' => $exception->getMessage(),
            ]);
        }

        //echo $exception->getMessage();
        //exit();

        return parent::render($request, $exception);
    }

    /**
     * @param Request $request
     * @return bool
     */
    protected function isApiRoute(Request $request): bool
    {
        return $request->route() && in_array('api', $request->route()->middleware());
    }
}
