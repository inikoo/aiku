<?php

namespace App\Exceptions;

use App\Actions\UI\Grp\GetFirstLoadProps;
use App\Http\Resources\UI\LoggedUserResource;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                app('sentry')->captureException($e);
            }
        });
    }

    public function render($request, Throwable $e): Response|JsonResponse|\Symfony\Component\HttpFoundation\Response|RedirectResponse
    {
        $response = parent::render($request, $e);

        if (!app()->environment(['local', 'testing'])
            && in_array($response->status(), [500, 503, 404, 403, 422])
            && !(!$request->inertia() && $request->expectsJson())
        ) {

            $errorData=match ($response->status()) {
                403 => [
                    'status'      => $response->status(),
                    'title'       => __('Forbidden'),
                    'description' => __('Sorry, you are forbidden from accessing this page.')
                ],
                404 => [
                    'status'      => $response->status(),
                    'title'       => __('Page Not Found'),
                    'description' => __('Sorry, the page you are looking for could not be found.')
                ],
                422 => [
                    'status'      => $response->status(),
                    'title'       => __('Unprocessable request'),
                    'description' => __('Sorry, is impossible to process this page.')
                ],
                503 => [
                    'status'      => $response->status(),
                    'title'       => __('Service Unavailable'),
                    'description' => __('Sorry, we are doing some maintenance. Please check back soon.')
                ],
                default => $this->getExceptionInfo()
            };
            $user=$request->user();
            if(Auth::check()) {
                $errorData= array_merge(
                    GetFirstLoadProps::run($user),
                    $errorData,
                    [
                    'auth'          => [
                        'user' => $request->user() ? new LoggedUserResource($user) : null,
                    ],
               ]
                );
            }

            return Inertia::render(
                Auth::check() ? 'Utils/ErrorInApp' : 'Utils/Error',
                $errorData
            )
                ->toResponse($request)
                ->setStatusCode($response->status());
        } elseif ($response->status() === 419) {
            return back()->with([
                                    'message' => 'The page expired, please try again.',
                                ]);
        }

        return $response;
    }

    public function getExceptionInfo(): array
    {
        return [
            'status'      => 500,
            'title'       => __('Server Error'),
            'description' => __('Whoops, something went wrong on our servers.')
        ];
    }
}
