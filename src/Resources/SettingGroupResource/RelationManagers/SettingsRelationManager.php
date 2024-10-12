<?php

namespace NinjaPortal\Admin\Resources\SettingGroupResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use NinjaPortal\Admin\Constants;

class SettingsRelationManager extends RelationManager
{
    protected static string $relationship = 'settings';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('key')
                    ->label('Key')
                    ->required()
                    ->disabled(fn ($record) => !is_null($record)),
                Forms\Components\TextInput::make('label')
                    ->label('Label')
                    ->required(),
                Forms\Components\Select::make('type')
                    ->label('Type')
                    ->options(Constants::SETTING_TYPES)
                    ->required(),
                Forms\Components\TextInput::make('value')
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
                Tables\Columns\TextColumn::make('key'),
                Tables\Columns\TextColumn::make('label'),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\TextColumn::make('value')
                    ->getStateUsing(function ($record) {
                        return is_null($record->value) ? config($record->key) : $value;
                    }),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
