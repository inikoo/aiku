<?php

/*
 * Author: stewicca <stewicalf@gmail.com>
 * Copyright (c) 2026, Steven Wicca Alfredo
 */

namespace App\Actions\Web\GoogleTagManager;

use App\Services\GoogleTagManager\GoogleTagManagerClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Lorisleiva\Actions\Concerns\AsAction;
use Sentry;
use Throwable;

/**
 * Google sends the person back here from the consent screen. The state was encrypted by
 * ConnectGoogleTagManager, so only a visit that started from the connect button for this group,
 * within the last quarter of an hour, can store a token.
 */
class CallbackGoogleTagManager
{
    use AsAction;

    public function asController(Request $request): RedirectResponse
    {
        $redirect = Redirect::route('grp.websites.google_tag_manager.index');
        $group    = GoogleTagManagerClient::groupFromState((string) $request->query('state'));

        if (!$group || blank($request->query('code'))) {
            return $redirect->with('notification', [
                'status'      => 'error',
                'title'       => __('Google Tag Manager not connected'),
                'description' => __('The connection was cancelled or has expired. Start again from the Connect button.'),
            ]);
        }

        try {
            GoogleTagManagerClient::storeRefreshToken($group, (string) $request->query('code'));
            FetchGoogleTagManagerContainers::dispatch($group);
        } catch (Throwable $exception) {
            Sentry::captureException($exception);

            return $redirect->with('notification', [
                'status'      => 'error',
                'title'       => __('Google Tag Manager not connected'),
                'description' => __('Google did not accept the connection. Please try again.'),
            ]);
        }

        return $redirect->with('notification', [
            'status'      => 'success',
            'title'       => __('Google Tag Manager connected'),
            'description' => __('The accounts and containers are being fetched.'),
        ]);
    }
}
