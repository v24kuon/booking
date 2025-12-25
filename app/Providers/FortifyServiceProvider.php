<?php

namespace App\Providers;

use App\Models\Member;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Fortify::loginView(fn () => view('auth.login'));

        Fortify::authenticateUsing(function (Request $request) {
            $request->validate([
                'member_mail' => ['required', 'string', 'email', 'max:50'],
                'password' => ['required', 'string'],
            ]);

            $email = (string) $request->input('member_mail');
            $password = (string) $request->input('password');

            $member = Member::query()
                ->where('member_mail', $email)
                ->first();

            if (! $member) {
                return null;
            }

            if (! Hash::check($password, (string) $member->member_password)) {
                return null;
            }

            // status: 1=本登録, 2=仮登録 を許可（8/9は拒否）
            if (! in_array((int) $member->status, [1, 2], true)) {
                return null;
            }

            return $member;
        });

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->input('member_mail', '');

            return Limit::perMinute(5)->by($email.$request->ip());
        });
    }
}
