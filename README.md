# NinjaPortal Admin Filament

`ninjaportal/admin-filament` provides the Filament-based admin panel for NinjaPortal.

It ships with ready-to-use resources for the core portal domains while keeping the panel open for extension and override in your application.

## Requirements

- PHP `^8.2`
- Laravel `^11.0 || ^12.0`
- Filament `^5.2`
- `ninjaportal/portal`

## What The Package Covers

The package currently includes Filament resources and pages for:

- Admin management
- User management
- Audience management
- API products
- Categories
- Roles
- Permissions
- Settings
- Setting groups
- User app management

It also includes:

- A dashboard page
- Portal overview stats widget
- Config-based resource and page overrides
- Service-backed create, update, and delete flows
- Translation-aware forms for translatable portal models

## Installation

Install the package with Composer:

```bash
composer require ninjaportal/admin-filament
```

The package auto-registers:

- `NinjaPortal\Admin\AdminFilamentServiceProvider`
- `NinjaPortal\Admin\PortalAdminPanelProvider`

Publish the configuration if you want to customize the panel:

```bash
php artisan vendor:publish --tag=portal-admin-config
```

## Configuration

The main config file is `config/portal-admin.php`.

Important options:

- `panel.id`: Filament panel identifier
- `panel.path`: admin panel path, default `admin`
- `panel.domain`: optional dedicated domain
- `panel.guard`: Filament session guard, default `admin_panel`
- `panel.provider`: auth provider used by the Filament guard
- `panel.password_broker`: password broker for the panel
- `panel.rbac_guard`: Spatie permission guard used for roles and permissions
- `panel.default`: whether this panel should be the default Filament panel
- `panel.login`: enables the built-in Filament login page
- `panel.profile`: enables the built-in profile page
- `panel.password_reset`: enables the built-in password reset pages
- `panel.navigation.*`: navigation group labels
- `panel.colors.*`: panel colors
- `panel.icons.*`: resource navigation icons
- `resources.*`: resource class and page override map
- `pages.*`: custom standalone panel pages
- `widgets`: dashboard widget list

## Guard And RBAC Model

This package intentionally separates:

- the Filament session/authentication guard
- the RBAC guard used by Spatie Permission

By default:

- `panel.guard = admin_panel`
- `panel.rbac_guard = admin`

This prevents collisions with `portal-api` installations while still allowing both packages to share the same admin RBAC layer.

The package also protects against misconfiguration and will throw if the Filament session guard is configured to reuse the RBAC/API admin guard.

## Write Operations Use Portal Services

All write-based resource operations should go through portal services instead of writing directly to Eloquent models.

That convention is built into the base resource/page flow:

- `NinjaPortal\Admin\Resources\PortalResource`
- `NinjaPortal\Admin\Resources\Pages\CreateRecordUsingService`
- `NinjaPortal\Admin\Resources\Pages\EditRecordUsingService`

Example:

```php
class CategoryResource extends PortalResource
{
    public static function service(): CategoryServiceInterface
    {
        return app(CategoryServiceInterface::class);
    }
}
```

`PortalResource::createUsingService()` and `PortalResource::updateUsingService()` delegate persistence to the configured service, which keeps:

- business rules in the `portal` package
- events firing consistently
- Filament resources thin and maintainable

## Overriding Resources And Pages

The package is designed so applications can replace the shipped resources and pages without forking the package.

Overrides are configured in `config/portal-admin.php`:

```php
'resources' => [
    'users' => [
        'resource' => \App\Admin\Resources\Users\UserResource::class,
        'pages' => [
            'index' => \App\Admin\Resources\Users\Pages\ListUsers::class,
            'create' => \App\Admin\Resources\Users\Pages\CreateUser::class,
            'edit' => \App\Admin\Resources\Users\Pages\EditUser::class,
            'apps' => \App\Admin\Resources\Users\Pages\ManageUserApps::class,
        ],
    ],
],
```

Internally, the package resolves overrides through `NinjaPortal\Admin\Support\ResourceRegistry`.

This means you can:

- replace a full resource class
- replace only specific pages
- keep the rest of the shipped admin panel intact

## Translatable Resources

Yes, translation handling is dynamic with respect to locales.

The translation tabs are built from `config('ninjaportal.locales')`, so when you add or remove locales, the translatable Filament form tabs change with them.

The reusable pieces are:

- `NinjaPortal\Admin\Support\TranslatableTabs`
- `NinjaPortal\Admin\Resources\Concerns\InteractsWithTranslatableData`

### How It Works

`TranslatableTabs` generates one tab per configured locale:

```php
$tabs = collect(config('ninjaportal.locales', ['en' => 'English']))
    ->map(fn (string $label, string $locale) => Tab::make($label)->schema($fields($locale)));
```

`InteractsWithTranslatableData` hydrates edit forms with existing translation rows by locale so the record can be edited naturally from the Filament form.

### How To Make A Resource Translatable

To build a translatable resource:

1. Use the `InteractsWithTranslatableData` trait on the resource
2. Build the form fields with `TranslatableTabs`
3. Name locale fields by locale key, for example `en.name`, `ar.name`
4. Keep write operations service-backed through `PortalResource`

Example resource:

```php
use Illuminate\Database\Eloquent\Model;
use NinjaPortal\Admin\Resources\Concerns\InteractsWithTranslatableData;
use NinjaPortal\Admin\Resources\PortalResource;
use NinjaPortal\Portal\Contracts\Services\CategoryServiceInterface;

class CategoryResource extends PortalResource
{
    use InteractsWithTranslatableData;

    public static function service(): CategoryServiceInterface
    {
        return app(CategoryServiceInterface::class);
    }

    public static function mutateFormDataBeforeFill(array $data, ?Model $record = null): array
    {
        return static::withTranslatableFormData($data, $record);
    }
}
```

Example form schema:

```php
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use NinjaPortal\Admin\Support\TranslatableTabs;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        $defaultLocale = array_key_first(config('ninjaportal.locales', ['en' => 'English'])) ?: 'en';

        return $schema->components([
            Section::make('Category')
                ->schema([
                    TextInput::make('slug')
                        ->required(),
                    TranslatableTabs::make(function (string $locale) use ($defaultLocale): array {
                        $isDefault = $locale === $defaultLocale;

                        return [
                            TextInput::make("{$locale}.name")
                                ->required($isDefault)
                                ->dehydratedWhenHidden(),
                        ];
                    }),
                ]),
        ]);
    }
}
```

### Important Note

The translation UI is locale-dynamic, but resource integration is still explicit.

That means:

- adding a new locale requires no form redesign
- adding a new translatable resource still requires you to opt into the helper pattern

This is intentional, because it keeps resource behavior explicit and predictable.

## Recommended Pattern For New Resources

When you add a new resource:

1. Create a `...Resource` class extending `PortalResource`
2. Split the form and table definitions into dedicated classes
3. Use a portal service contract for writes
4. Add resource/page overrides to config only if your application needs them
5. If the model is translatable, use `TranslatableTabs` and `InteractsWithTranslatableData`

## Testing

The package ships with its own PHPUnit config and supports:

```bash
composer test
composer analyse
composer format:test
```

## Notes

- This package focuses on the Filament admin experience only.
- The domain logic remains in `ninjaportal/portal`.
- If you need custom admin behavior, prefer overriding resources/pages instead of editing vendor code.
