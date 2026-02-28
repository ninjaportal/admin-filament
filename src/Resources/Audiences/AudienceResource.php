<?php

namespace NinjaPortal\Admin\Resources\Audiences;

use Filament\Resources\Pages\PageRegistration;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use NinjaPortal\Admin\Resources\Audiences\Pages\CreateAudience;
use NinjaPortal\Admin\Resources\Audiences\Pages\EditAudience;
use NinjaPortal\Admin\Resources\Audiences\Pages\ListAudiences;
use NinjaPortal\Admin\Resources\Audiences\Schemas\AudienceForm;
use NinjaPortal\Admin\Resources\Audiences\Tables\AudiencesTable;
use NinjaPortal\Admin\Resources\PortalResource;
use NinjaPortal\Portal\Contracts\Services\AudienceServiceInterface;
use NinjaPortal\Portal\Models\Audience;
use NinjaPortal\Portal\Utils;

class AudienceResource extends PortalResource
{
    public static function getModel(): string
    {
        return Utils::getAudienceModel() ?: Audience::class;
    }

    public static function getResourceKey(): string
    {
        return 'audiences';
    }

    public static function service(): AudienceServiceInterface
    {
        return app(AudienceServiceInterface::class);
    }

    public static function form(Schema $schema): Schema
    {
        return AudienceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AudiencesTable::configure($table, static::class);
    }

    /**
     * @return array<string, PageRegistration>
     */
    public static function getPages(): array
    {
        return [
            'index' => static::page('index', ListAudiences::class)::route('/'),
            'create' => static::page('create', CreateAudience::class)::route('/create'),
            'edit' => static::page('edit', EditAudience::class)::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return (string) config('portal-admin.panel.navigation.users', __('portal-admin::portal-admin.navigation.users'));
    }

    public static function mutateFormDataBeforeFill(array $data, ?Model $record = null): array
    {
        if ($record instanceof Audience) {
            $record->loadMissing(['users', 'products']);
            $data['user_ids'] = $record->users->pluck('id')->all();
            $data['api_product_ids'] = $record->products->pluck('id')->all();
        }

        return $data;
    }

    public static function createUsingService(array $data): Model
    {
        $userIds = Arr::pull($data, 'user_ids', []);
        $apiProductIds = Arr::pull($data, 'api_product_ids', []);

        /** @var Audience $audience */
        $audience = parent::createUsingService($data);

        if (is_array($userIds)) {
            $audience = static::service()->syncUsers($audience, $userIds);
        }

        if (is_array($apiProductIds)) {
            $audience = static::service()->syncProducts($audience, $apiProductIds);
        }

        $audience->loadMissing(['users', 'products']);

        return $audience;
    }

    public static function updateUsingService(Model $record, array $data): Model
    {
        $userIds = Arr::pull($data, 'user_ids', null);
        $apiProductIds = Arr::pull($data, 'api_product_ids', null);

        /** @var Audience $audience */
        $audience = parent::updateUsingService($record, $data);

        if (is_array($userIds)) {
            $audience = static::service()->syncUsers($audience, $userIds);
        }

        if (is_array($apiProductIds)) {
            $audience = static::service()->syncProducts($audience, $apiProductIds);
        }

        $audience->loadMissing(['users', 'products']);

        return $audience;
    }
}
