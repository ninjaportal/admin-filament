<?php

namespace NinjaPortal\Admin\Resources\User\Tables;

use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use NinjaPortal\Admin\Resources\User\Pages\UserAppsPage;
use NinjaPortal\Portal\Models\User;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label(__('Full Name')),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('sync_with_apigee')
                    ->label(__('Synced with Apigee'))
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No')
                    ->colors([
                        true => 'success',
                        false => 'danger',
                    ]),
                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::$ACTIVE_STATUS => 'success',
                        User::$INACTIVE_STATUS => 'danger',
                        default => 'gray',
                    })
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('apps')
                    ->color('info')
                    ->icon('heroicon-o-cube')
                    ->url(fn (Model $record) => UserAppsPage::getUrl(['record' => $record->getKey()]))
                    ->label(__('Apps')),
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
