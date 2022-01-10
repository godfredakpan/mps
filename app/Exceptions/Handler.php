<?php

namespace App\Exceptions;

use Throwable;
use Livewire\ComponentConcerns\ReceivesEvents;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;

class Handler extends ExceptionHandler
{
    use ReceivesEvents;

    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    protected $dontReport = [];

    public function register()
    {
        //
    }

    public function render($request, Throwable $e)
    {
        $response = parent::render($request, $e);

        if (!app()->environment('local') && in_array($response->status(), [500, 503, 404, 403, 419])) {
            if ($request->ajax()) {
                return response()->json(['error' => $this->errorTitle($response->status()) . ' ' . $this->errorDescription($response->status())], $response->status());
            }
            return back()->with([
                'error' => $this->errorTitle($response->status()) . ' ' . $this->errorDescription($response->status()),
            ]);
        }

        return $response;
    }

    private function errorDescription($status)
    {
        return [
            '503' => __('Sorry, we are doing some maintenance. Please check back soon.'),
            '500' => __('Whoops, something went wrong on our servers.'),
            '404' => __('Sorry, the page you are looking for could not be found.'),
            '403' => __('Sorry, you are forbidden from accessing this page.'),
            '419' => __('Sorry, the page has expired, please refresh and try again.'),
        ][$status];
    }

    private function errorTitle($status)
    {
        return [
            '503' => __('503: Service Unavailable'),
            '500' => __('500: Server Error'),
            '404' => __('404: Page Not Found'),
            '403' => __('403: Forbidden'),
            '419' => __('403: Page Expired'),
        ][$status];
    }
}
