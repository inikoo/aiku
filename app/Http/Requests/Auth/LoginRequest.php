<?php
/*
 *  Author: Raul Perusquia <raul@inikoo.com>
 *  Created: Tue, 06 Sept 2022 18:56:46 Malaysia Time, Kuala Lumpur, Malaysia
 *  Copyright (c) 2022, Raul A Perusquia Flores
 */

namespace App\Http\Requests\Auth;

use App\Actions\Auth\User\Hydrators\UserHydrateAuth;
use App\Models\Auth\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }


    public function rules(): array
    {
        return [
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @return void
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();


        if (!Auth::attempt(
            array_merge($this->validated(), ['status' => true]),
            $this->boolean('remember')
        )) {
            RateLimiter::hit($this->throttleKey());


            UserHydrateAuth::dispatch(User::where($this->validated()['email'])->first());

            throw ValidationException::withMessages([
                                                        'username' => trans('auth.failed'),
                                                    ]);
        }


        UserHydrateAuth::dispatch(User::where($this->validated()['email'])->first());
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @return void
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
                                                    'login' => trans('auth.throttle', [
                                                        'seconds' => $seconds,
                                                        'minutes' => ceil($seconds / 60),
                                                    ]),
                                                ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     *
     * @return string
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->input('login')).'|'.$this->ip());
    }
}
