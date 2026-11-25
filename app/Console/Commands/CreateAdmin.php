<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create';

    protected $description = 'Create or promote an administrator account';

    public function handle(): int
    {
        $name = $this->ask('Nama admin');
        $email = $this->ask('Email admin');
        $password = $this->secret('Kata sandi admin (minimal 8 karakter)');
        $confirmation = $this->secret('Ulangi kata sandi');

        $validator = Validator::make(
            compact('name', 'email', 'password', 'confirmation'),
            [
                'name' => ['required', 'string', 'max:120'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', Password::min(8)],
                'confirmation' => ['required', 'same:password'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->components->error($error);
            }

            return self::FAILURE;
        }

        $admin = User::firstOrNew(['email' => $email]);
        $isNew = ! $admin->exists;
        $admin->name = $name;
        $admin->role = 'admin';
        if ($isNew) {
            $admin->password = Hash::make($password);
            $admin->email_verified_at = now();
        }
        $admin->save();

        $this->components->info($isNew ? 'Akun admin berhasil dibuat.' : 'Akun diubah menjadi admin tanpa mengganti kata sandinya.');

        return self::SUCCESS;
    }
}
