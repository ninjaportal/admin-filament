<?php

namespace NinjaPortal\Admin\Tests\Feature;

use Livewire\Livewire;
use Mockery;
use NinjaPortal\Admin\Resources\ApiProducts\Pages\CreateApiProduct;
use NinjaPortal\Admin\Tests\TestCase;
use NinjaPortal\Portal\Contracts\Services\ApiProductServiceInterface;
use NinjaPortal\Portal\Models\ApiProduct;
use NinjaPortal\Portal\Models\Audience;
use NinjaPortal\Portal\Models\Category;

class ApiProductResourceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_create_api_product_page_uses_service_and_preserves_translation_payloads(): void
    {
        $category = Category::query()->create(['slug' => 'payments']);
        $category->fill([
            'en' => ['name' => 'Payments'],
            'ar' => ['name' => 'المدفوعات'],
        ])->save();

        $audience = Audience::query()->create([
            'name' => 'Partners',
        ]);

        $apigeeProduct = new class
        {
            public function getName(): string
            {
                return 'payments-api';
            }
        };

        $service = Mockery::mock(ApiProductServiceInterface::class);
        $service->shouldReceive('apigeeProducts')
            ->atLeast()
            ->once()
            ->andReturn(collect([$apigeeProduct]));

        $service->shouldReceive('create')
            ->once()
            ->with(Mockery::on(function (array $data): bool {
                return $data['slug'] === 'payments-api'
                    && $data['apigee_product_id'] === 'payments-api'
                    && $data['visibility'] === 'public'
                    && ($data['en']['name'] ?? null) === 'Payments API'
                    && ($data['ar']['name'] ?? null) === 'واجهة المدفوعات';
            }))
            ->andReturnUsing(function (array $data): ApiProduct {
                return ApiProduct::query()->create($data);
            });

        $service->shouldReceive('syncCategories')
            ->once()
            ->with(Mockery::type(ApiProduct::class), [$category->getKey()])
            ->andReturnUsing(function (ApiProduct $apiProduct, array $categoryIds): ApiProduct {
                $apiProduct->categories()->sync($categoryIds);

                return $apiProduct->fresh('categories');
            });

        $service->shouldReceive('syncAudiences')
            ->once()
            ->with(Mockery::type(ApiProduct::class), [$audience->getKey()])
            ->andReturnUsing(function (ApiProduct $apiProduct, array $audienceIds): ApiProduct {
                $apiProduct->audiences()->sync($audienceIds);

                return $apiProduct->fresh('audiences');
            });

        $this->app->instance(ApiProductServiceInterface::class, $service);

        Livewire::test(CreateApiProduct::class)
            ->fillForm([
                'slug' => 'payments-api',
                'apigee_product_id' => 'payments-api',
                'visibility' => 'public',
                'category_ids' => [$category->getKey()],
                'audience_ids' => [$audience->getKey()],
                'tags' => ['payments', 'billing'],
                'custom_attributes' => ['team' => 'billing'],
                'en' => [
                    'name' => 'Payments API',
                    'short_description' => 'Payment APIs',
                    'description' => '<p>English description</p>',
                ],
                'ar' => [
                    'name' => 'واجهة المدفوعات',
                    'short_description' => 'واجهات الدفع',
                    'description' => '<p>وصف عربي</p>',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('api_products', [
            'slug' => 'payments-api',
            'apigee_product_id' => 'payments-api',
        ]);
    }
}
