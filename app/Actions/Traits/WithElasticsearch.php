<?php
/*
 * Author: Artha <artha@aw-advantage.com>
 * Created: Fri, 26 May 2023 13:25:37 Central Indonesia Time, Sanur, Bali, Indonesia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */


namespace App\Actions\Traits;

use App\Models\Auth\User;
use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Http\Request;

trait WithElasticsearch
{
    /**
     * @throws \Elastic\Elasticsearch\Exception\AuthenticationException
     */
    public function init(): Client
    {
        return ClientBuilder::create()->build();
    }

    public function storeElastic(Request $request): void
    {
        /** @var User $user */
        $user = auth()->user();

        $data =  [
            'datetime' => now(),
            'tenant_slug' => app('currentTenant')->slug,
            'username' => $user->username,
            'route' => [
                'name' => $request->route()->getName(),
                'parameters' => $request->route()->parameters
            ],
            'ip_address' => $request->ip()
        ];

        $this->init()->index([
            'index' => $data['tenant_slug'],
            'body' => $data
        ]);
    }
}
