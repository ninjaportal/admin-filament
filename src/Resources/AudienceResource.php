<?php

namespace NinjaPortal\Admin\Resources;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use NinjaPortal\Admin\Concerns\HasNinjaService;
use NinjaPortal\Admin\Constants;
use NinjaPortal\Admin\Resources\AudienceResource\Pages;
use NinjaPortal\Portal\Models\Audience;
use NinjaPortal\Portal\Services\AudienceService;
use NinjaPortal\Portal\Services\IService;

class AudienceResource extends Resource
{

    use HasNinjaService;


    protected static ?string $model = Audience::class;

    protected static ?string $slug = 'audiences';

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->unique()
                        ->regex('/^[a-zA-Z0-9_]+$/')
                        ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label(__('Name'))
                    ->sortable(),
            ])
            ->filters([

            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAudiences::route('/'),
            'create' => Pages\CreateAudience::route('/create'),
            'edit' => Pages\EditAudience::route('/{record}/edit'),
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

    public static function service(): IService
    {
        return new AudienceService();
    }

    public static function getNavigationGroup(): ?string
    {
        return __("ninjaadmin::ninjaadmin.navigation_groups.".Constants::NAVIGATION_GROUPS['USER']);
    }
}
