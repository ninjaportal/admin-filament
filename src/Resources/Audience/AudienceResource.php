<?php

namespace NinjaPortal\Admin\Resources\Audience;

use Filament\Schemas\Schema;
use NinjaPortal\Admin\Resources\Audience\Pages\ListAudiences;
use NinjaPortal\Admin\Resources\Audience\Pages\CreateAudience;
use NinjaPortal\Admin\Resources\Audience\Pages\EditAudience;
use Filament\Resources\Resource;
use Filament\Tables\Table;
use NinjaPortal\Admin\Concerns\HasNinjaService;
use NinjaPortal\Admin\Constants;
use NinjaPortal\Admin\Resources\Audience\Schemas\AudienceForm;
use NinjaPortal\Admin\Resources\Audience\Tables\AudiencesTable;
use NinjaPortal\Admin\Resources\Audience\Pages;
use NinjaPortal\Portal\Models\Audience;
use NinjaPortal\Portal\Services\AudienceService;
use NinjaPortal\Portal\Contracts\Services\ServiceInterface;

class AudienceResource extends Resource
{

    use HasNinjaService;


    protected static ?string $model = Audience::class;

    protected static ?string $slug = 'audiences';

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-circle';


    public static function form(Schema $schema): Schema
    {
        return AudienceForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AudiencesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAudiences::route('/'),
            'create' => CreateAudience::route('/create'),
            'edit' => EditAudience::route('/{record}/edit'),
        ];
    }

    public static function getLabel(): ?string
    {
        return __('Audiences');
    }

    public static function singularLabel(): ?string
    {
        return __('Audience');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    public static function service(): ServiceInterface
    {
        return new AudienceService();
    }

    public static function getNavigationGroup(): ?string
    {
        return __("ninjaadmin::ninjaadmin.navigation_groups.".Constants::NAVIGATION_GROUPS['USER']);
    }
}
