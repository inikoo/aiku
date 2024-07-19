<?php

namespace App\Exceptions;

use App\Actions\UI\Grp\GetFirstLoadProps;
use App\Http\Resources\UI\LoggedUserResource;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
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
            && in_array($response->getStatusCode(), [500, 503, 404, 403, 422])
            && !(!$request->inertia() && $request->expectsJson())
        ) {

            if(str_starts_with($request->route()->getName(), 'grp.models')) {

                return back()->withErrors([
                    'error_in_models' => $response->getStatusCode().': '.$e->getMessage()
                ]);
            }

            $errorData=match ($response->getStatusCode()) {
                403 => [
                    'status'      => $response->getStatusCode(),
                    'title'       => __('Forbidden'),
                    'description' => __('Sorry, you are forbidden from accessing this page.')
                ],
                404 => [
                    'status'      => $response->getStatusCode(),
                    'title'       => __('Page Not Found'),
                    'description' => __('Sorry, the page you are looking for could not be found.')
                ],
                422 => [
                    'status'      => $response->getStatusCode(),
                    'title'       => __('Unprocessable request'),
                    'description' => __('Sorry, is impossible to process this page.')
                ],
                503 => [
                    'status'      => $response->getStatusCode(),
                    'title'       => __('Service Unavailable'),
                    'description' => __('Sorry, we are doing some maintenance. Please check back soon.')
                ],
                default => $this->getExceptionInfo($e)
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

            $host = Request::getHost();



            if ($host == 'app.'.config('app.domain')) {
                Inertia::setRootView('app-grp');
                $app='grp';
            } else {
                $path = Request::path();
                if (preg_match('/^app\//', $path)) {
                    Inertia::setRootView('app-retina');
                    $app='retina';
                } else {
                    Inertia::setRootView('app-iris');
                    $app='iris';
                }
            }


            return Inertia::render(
                $this->getInertiaPage($e, $app),
                $errorData
            )
                ->toResponse($request)
                ->setStatusCode($response->getStatusCode());
        } elseif ($response->getStatusCode() === 419) {
            return back()->with([
                                    'message' => 'The page expired, please try again.',
                                ]);
        }

        return $response;
    }

    public function getExceptionInfo(Throwable $e): array
    {
        if (get_class($e) == 'App\Exceptions\IrisWebsiteNotFound') {
            return [
                'status'      => 404,
                'title'       => __('Domain Not Found'),
                'description' => __('This domain was not been configured yet.')
            ];
        }

        return [
            'status'      => 500,
            'title'       => __('Server Error'),
            'description' => __('Whoops, something went wrong on our servers.')
        ];
    }

    public function getInertiaPage(Throwable $e, string $app): string
    {


        if (get_class($e) == 'App\Exceptions\IrisWebsiteNotFound' and $app=='iris') {
            return 'Errors/IrisWebsiteNotFound';
        }

        $page='Errors/Error';

        if($app=='grp' or $app=='retina') {



            $page= Auth::check() ? 'Errors/ErrorInApp' : 'Errors/Error';
        }

        return $page;

    }
}
