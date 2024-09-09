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
use NinjaPortal\Admin\Constants;
use NinjaPortal\Portal\Models\SettingGroup;
use NinjaPortal\Admin\Resources\SettingGroupResource\Pages;
use NinjaPortal\Admin\Resources\SettingGroupResource\RelationManagers\SettingsRelationManager;

class SettingGroupResource extends Resource
{
    protected static ?string $model = SettingGroup::class;

    protected static ?string $slug = 'settings';

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()->schema([
                    TextInput::make('name')
                        ->label(__('Group Name'))
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
                    ->label(__('Group Name')),
            ])
            ->filters([
                //
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
            'index' => Pages\ListSettingGroups::route('/'),
            'create' => Pages\CreateSettingGroup::route('/create'),
            'edit' => Pages\EditSettingGroup::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [];
    }

    public static function getLabel(): string
    {
        return __('Settings');
    }


    public static function getRelations(): array
    {
        return [
            SettingsRelationManager::make()
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return __(Constants::NAVIGATION_GROUPS['ADMIN']);
    }

}
