<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateAdmin extends Command
{
    protected $signature = 'admin:create';
    protected $description = 'Create a new administrator using private console prompts';

    public function handle(): int
    {
        $name = $this->ask('Name');
        $email = Str::lower(trim((string) $this->ask('Email')));
        $password = $this->secret('Password (at least 12 characters)');
        $confirmation = $this->secret('Confirm password');
        $validator = Validator::make(
            compact('name', 'email', 'password') + ['password_confirmation' => $confirmation],
            ['name' => 'required|string|max:255', 'email' => 'required|email|max:255|unique:users',
             'password' => 'required|string|min:12|max:72|confirmed']
        );
        if ($validator->fails()) {
            $this->error($validator->errors()->first());
            return self::FAILURE;
        }

        $admin = new User(compact('name', 'email', 'password'));
        $admin->is_admin = true;
        $admin->save();
        $this->info('Administrator created.');
        return self::SUCCESS;
    }
}
