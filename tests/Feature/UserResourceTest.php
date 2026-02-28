<?php

namespace NinjaPortal\Admin\Tests\Feature;

use Livewire\Livewire;
use Mockery;
use NinjaPortal\Admin\Resources\Users\Pages\CreateUser;
use NinjaPortal\Admin\Tests\TestCase;
use NinjaPortal\Portal\Contracts\Services\UserServiceInterface;
use NinjaPortal\Portal\Models\Audience;
use NinjaPortal\Portal\Models\User;

class UserResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_create_user_page_writes_through_the_user_service(): void
    {
        $audience = Audience::query()->create([
            'name' => 'Early Access',
        ]);

        $service = Mockery::mock(UserServiceInterface::class);
        $service->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data): bool {
                return $data['email'] === 'developer@example.com'
                    && $data['first_name'] === 'Ada'
                    && $data['last_name'] === 'Lovelace'
                    && $data['status'] === User::$ACTIVE_STATUS;
            }))
            ->andReturnUsing(function (array $data): User {
                return User::query()->create($data);
            });

        $service->shouldReceive('syncAudiences')
            ->once()
            ->with(Mockery::type(User::class), [$audience->getKey()])
            ->andReturnUsing(function (User $user, array $audienceIds): User {
                $user->audiences()->sync($audienceIds);

                return $user->fresh('audiences');
            });

        $this->app->instance(UserServiceInterface::class, $service);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'email' => 'developer@example.com',
                'password' => 'secret-password',
                'status' => User::$ACTIVE_STATUS,
                'sync_with_apigee' => true,
                'audience_ids' => [$audience->getKey()],
                'custom_attributes' => ['team' => 'platform'],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'developer@example.com',
            'first_name' => 'Ada',
            'last_name' => 'Lovelace',
        ]);
        $this->assertDatabaseHas('audience_user', [
            'audience_id' => $audience->getKey(),
        ]);
    }
}
