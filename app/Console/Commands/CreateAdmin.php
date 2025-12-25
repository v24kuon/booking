<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create
        {--name= : 管理者名}
        {--email= : メールアドレス}
        {--password= : パスワード}
        {--force : 既存emailがある場合に上書きする}';

    protected $description = '管理者アカウント（admins）を作成します';

    public function handle(): int
    {
        $name = (string) ($this->option('name') ?: $this->ask('管理者名', 'admin'));
        $email = (string) ($this->option('email') ?: $this->ask('メールアドレス'));
        $password = (string) ($this->option('password') ?: $this->secret('パスワード'));

        if ($email === '' || $password === '') {
            $this->error('email と password は必須です。');

            return self::FAILURE;
        }

        $existing = Admin::query()->where('email', $email)->first();
        if ($existing && ! $this->option('force')) {
            $this->error('同じメールアドレスの管理者が既に存在します。上書きする場合は --force を指定してください。');

            return self::FAILURE;
        }

        $admin = Admin::updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => Hash::make($password),
            ]
        );

        $this->info("管理者を作成しました: id={$admin->id}, email={$admin->email}");

        return self::SUCCESS;
    }
}
