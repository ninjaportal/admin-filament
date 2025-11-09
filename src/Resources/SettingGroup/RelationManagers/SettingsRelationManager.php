<?php

namespace NinjaPortal\Admin\Resources\SettingGroup\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use NinjaPortal\Admin\Constants;

class SettingsRelationManager extends RelationManager
{
    protected static string $relationship = 'settings';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('key')
                    ->label('Key')
                    ->required()
                    ->disabled(fn ($record) => !is_null($record)),
                TextInput::make('label')
                    ->label('Label')
                    ->required(),
                Select::make('type')
                    ->label('Type')
                    ->options(Constants::SETTING_TYPES)
                    ->required(),
                TextInput::make('value')
                    ->label('Value')
                    ->required(),
            ])
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('key'),
                TextColumn::make('label'),
                TextColumn::make('type'),
                TextColumn::make('value')
                    ->getStateUsing(function ($record) {
                        return $record->value ?? config($record->key);
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
