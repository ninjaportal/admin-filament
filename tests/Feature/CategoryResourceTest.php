<?php

namespace NinjaPortal\Admin\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use NinjaPortal\Admin\Resources\Categories\Pages\CreateCategory;
use NinjaPortal\Admin\Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    public function test_create_category_page_persists_each_locale_translation(): void
    {
        Livewire::test(CreateCategory::class)
            ->fillForm([
                'slug' => 'payments-multilingual',
                'en' => [
                    'name' => 'Payments',
                    'short_description' => 'English short',
                ],
                'ar' => [
                    'name' => 'المدفوعات',
                    'short_description' => 'وصف عربي',
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('categories', [
            'slug' => 'payments-multilingual',
        ]);

        $categoryId = (int) DB::table('categories')
            ->where('slug', 'payments-multilingual')
            ->value('id');

        $this->assertDatabaseHas('category_translations', [
            'category_id' => $categoryId,
            'locale' => 'en',
            'name' => 'Payments',
            'short_description' => 'English short',
        ]);

        $this->assertDatabaseHas('category_translations', [
            'category_id' => $categoryId,
            'locale' => 'ar',
            'name' => 'المدفوعات',
            'short_description' => 'وصف عربي',
        ]);
    }
}
