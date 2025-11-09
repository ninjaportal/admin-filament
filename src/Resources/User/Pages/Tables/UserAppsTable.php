<?php

namespace NinjaPortal\Admin\Resources\User\Pages\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use NinjaPortal\Admin\Resources\User\Pages\UserAppsPage;

class UserAppsTable
{
    public static function configure(Table $table, UserAppsPage $page): Table
    {
        return $table
            ->deferLoading()
            ->records(fn () => $page->getAppTableRecords())
            ->columns([
                TextColumn::make('displayName'),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->formatStateUsing(function ($state, array $record) {
                        return str($record['status'])->title();
                    })
                    ->colors([
                        'success' => fn ($state, array $record) => $record['status'] === UserAppsPage::APPROVED_STATUS,
                        'danger' => fn ($state, array $record) => $record['status'] === UserAppsPage::REVOKED_STATUS,
                    ]),
                TextColumn::make('createdAt')
                    ->label(__('Created At')),
            ])
            ->recordActions([
                $page->manageAppAction(),
                $page->deleteAppAction(),
            ]);
    }
}
