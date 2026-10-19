<?php

namespace App\Console\Commands;

use App\Models\Business;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Creates the owner account of a business. The password is read from a hidden
 * prompt and is never accepted as a CLI argument.
 */
#[Signature('sme:create-admin {--email= : Admin email address} {--name= : Display name} {--business= : Business slug} {--business-name= : Create the business with this name when the slug does not exist yet}')]
#[Description('Create a business owner account (password prompted, never an argument)')]
class CreateAdminCommand extends Command
{
    private const PASSWORD_MIN = 12;

    public function handle(): int
    {
        $slug = (string) ($this->option('business') ?: $this->ask('Business slug'));
        $business = Business::where('slug', $slug)->first();

        if (! $business) {
            $businessName = $this->option('business-name');

            if (blank($businessName)) {
                $this->components->error("No business with the slug \"{$slug}\". Pass --business-name to create it.");

                return self::FAILURE;
            }

            $business = Business::create([
                'name' => $businessName,
                'slug' => Business::generateUniqueSlug($slug ?: $businessName),
                'public_status' => 'published',
            ]);
        }

        $email = (string) ($this->option('email') ?: $this->ask('Email'));
        $name = (string) ($this->option('name') ?: $this->ask('Name'));
        $password = (string) $this->secret('Password (min '.self::PASSWORD_MIN.' characters)');
        $confirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make(
            ['name' => $name, 'email' => $email, 'password' => $password, 'password_confirmation' => $confirmation],
            [
                'name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'password' => ['required', 'string', 'min:'.self::PASSWORD_MIN, 'confirmed'],
            ],
        );

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $message) {
                $this->components->error($message);
            }

            return self::FAILURE;
        }

        $user = User::create(['name' => $name, 'email' => $email, 'password' => $password]);
        $user->forceFill(['email_verified_at' => now()])->save();

        $business->users()->syncWithoutDetaching([$user->id => ['role' => 'owner', 'status' => 'active']]);

        $this->components->info("Admin {$user->email} created.");

        return self::SUCCESS;
    }
}
