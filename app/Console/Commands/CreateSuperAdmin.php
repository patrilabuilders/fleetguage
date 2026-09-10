<?php

namespace App\Console\Commands;

use App\Models\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'super-admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Super Admin account securely from the command line';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('--- SaaS Super Admin Creator ---');

        // 1. Prompt for Name
        $name = $this->ask('Enter Super Admin Name');
        if (empty($name)) {
            $this->error('Name is required.');

            return self::FAILURE;
        }

        // 2. Prompt for Email
        $email = $this->ask('Enter Super Admin Email Address');

        // Validate Email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email|unique:admins,email',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // 3. Prompt for Password (masked)
        $password = $this->secret('Enter Password (at least 8 characters)');
        $passwordConfirm = $this->secret('Confirm Password');

        if ($password !== $passwordConfirm) {
            $this->error('Passwords do not match.');

            return self::FAILURE;
        }

        // Validate Password
        $validatorPassword = Validator::make(['password' => $password], [
            'password' => 'required|string|min:8',
        ]);

        if ($validatorPassword->fails()) {
            foreach ($validatorPassword->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        // 4. Create the Super Admin account
        Admin::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
        ]);

        $this->info("Super Admin account for {$name} ({$email}) was created successfully!");

        return self::SUCCESS;
    }
}
