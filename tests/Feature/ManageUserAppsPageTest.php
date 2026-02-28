<?php

namespace NinjaPortal\Admin\Tests\Feature;

use Livewire\Livewire;
use Mockery;
use NinjaPortal\Admin\Resources\Users\Pages\ManageUserApps;
use NinjaPortal\Admin\Tests\TestCase;
use NinjaPortal\Portal\Contracts\Services\ApiProductServiceInterface;
use NinjaPortal\Portal\Contracts\Services\UserAppCredentialServiceInterface;
use NinjaPortal\Portal\Contracts\Services\UserAppServiceInterface;
use NinjaPortal\Portal\Models\User;

class ManageUserAppsPageTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_manage_user_apps_page_uses_services_to_create_apps(): void
    {
        $user = User::query()->create([
            'first_name' => 'Portal',
            'last_name' => 'Developer',
            'email' => 'developer.apps@example.com',
            'password' => 'secret-password',
            'status' => User::$ACTIVE_STATUS,
        ]);

        $product = new class
        {
            public function getName(): string
            {
                return 'payments-api';
            }
        };

        $apps = Mockery::mock(UserAppServiceInterface::class);
        $apps->shouldReceive('all')
            ->atLeast()
            ->once()
            ->with($user->email)
            ->andReturn(collect());
        $apps->shouldReceive('create')
            ->once()
            ->with($user->email, Mockery::on(function (array $payload): bool {
                return $payload['name'] === 'portal-app'
                    && $payload['displayName'] === 'Portal App'
                    && $payload['status'] === 'approved'
                    && $payload['apiProducts'] === ['payments-api'];
            }))
            ->andReturnNull();

        $credentials = Mockery::mock(UserAppCredentialServiceInterface::class);

        $catalog = Mockery::mock(ApiProductServiceInterface::class);
        $catalog->shouldReceive('apigeeProducts')
            ->once()
            ->andReturn(collect([$product]));

        $this->app->instance(UserAppServiceInterface::class, $apps);
        $this->app->instance(UserAppCredentialServiceInterface::class, $credentials);
        $this->app->instance(ApiProductServiceInterface::class, $catalog);

        Livewire::test(ManageUserApps::class, [
            'record' => $user->getKey(),
        ])
            ->assertOk()
            ->assertSet('apiProducts', ['payments-api' => 'payments-api'])
            ->callAction('createApp', data: [
                'name' => 'portal-app',
                'displayName' => 'Portal App',
                'callbackUrl' => 'https://example.com/callback',
                'description' => 'Portal application',
                'apiProducts' => ['payments-api'],
                'status' => 'approved',
            ])
            ->assertHasNoActionErrors();
    }

    public function test_manage_user_apps_page_preserves_existing_app_metadata_when_updating(): void
    {
        $user = User::query()->create([
            'first_name' => 'Portal',
            'last_name' => 'Developer',
            'email' => 'developer.manage@example.com',
            'password' => 'secret-password',
            'status' => User::$ACTIVE_STATUS,
        ]);

        $product = new class
        {
            public function getName(): string
            {
                return 'payments-api';
            }
        };

        $app = new class
        {
            public function getName(): string
            {
                return 'portal-app';
            }

            public function getDisplayName(): string
            {
                return 'Portal App';
            }

            public function getCallbackUrl(): string
            {
                return 'https://example.com/callback';
            }

            public function getDescription(): string
            {
                return 'Portal application';
            }

            public function getStatus(): string
            {
                return 'approved';
            }

            public function getCreatedAt(): string
            {
                return '2026-02-28 12:00:00';
            }

            public function getApiProducts(): array
            {
                return ['payments-api'];
            }

            public function getCredentials(): array
            {
                return [];
            }
        };

        $apps = Mockery::mock(UserAppServiceInterface::class);
        $apps->shouldReceive('all')
            ->twice()
            ->with($user->email)
            ->andReturn(collect([$app]), collect([$app]));
        $apps->shouldReceive('update')
            ->once()
            ->with($user->email, 'portal-app', Mockery::on(function (array $payload): bool {
                return $payload['name'] === 'portal-app'
                    && $payload['displayName'] === 'Portal App'
                    && $payload['callbackUrl'] === 'https://example.com/callback'
                    && $payload['description'] === 'Portal application'
                    && $payload['status'] === 'revoked'
                    && ! array_key_exists('apiProducts', $payload);
            }))
            ->andReturnNull();
        $apps->shouldReceive('revoke')
            ->once()
            ->with($user->email, 'portal-app')
            ->andReturnNull();

        $credentials = Mockery::mock(UserAppCredentialServiceInterface::class);

        $catalog = Mockery::mock(ApiProductServiceInterface::class);
        $catalog->shouldReceive('apigeeProducts')
            ->once()
            ->andReturn(collect([$product]));

        $this->app->instance(UserAppServiceInterface::class, $apps);
        $this->app->instance(UserAppCredentialServiceInterface::class, $credentials);
        $this->app->instance(ApiProductServiceInterface::class, $catalog);

        Livewire::test(ManageUserApps::class, [
            'record' => $user->getKey(),
        ])
            ->assertOk()
            ->callTableAction('manage', '0', data: [
                'name' => 'portal-app',
                'displayName' => 'Portal App',
                'callbackUrl' => 'https://example.com/callback',
                'description' => 'Portal application',
                'status' => 'revoked',
                'credentials' => [],
            ])
            ->assertHasNoActionErrors();
    }
}
