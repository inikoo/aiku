<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Wed, 10 Aug 2022 15:45:58 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Inikoo
 *  Version 4.0
 */

namespace App\Http\Controllers;

use App\Models\Auth\User;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FacebookAuthController extends Controller
{
    public function facebookRedirect(): RedirectResponse|\Illuminate\Http\RedirectResponse
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function loginWithFacebook()
    {
        try {

            $user = Socialite::driver('facebook')->user();
            $isUser = User::where('facebook_id', $user->id)->first();

            if($isUser){
                Auth::login($isUser);
            }else{
                $createUser = User::create([
                                               'name' => $user->name,
                                               'email' => $user->email,
                                               'facebook_id' => $user->id,
                                               'password' => Str::random(64)
                                           ]);

                Auth::login($createUser);
            }

            return redirect('/dashboard');
        } catch (Exception $exception) {
            dd($exception->getMessage());
        }
    }
}
