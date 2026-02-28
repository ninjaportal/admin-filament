<?php

namespace NinjaPortal\Admin\Resources\Users\Pages\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use NinjaPortal\Admin\Resources\Users\Pages\ManageUserApps;

class UserAppsTable
{
    public static function configure(Table $table, ManageUserApps $page): Table
    {
        return $table
            ->records(fn () => collect($page->getAppRecords()))
            ->columns([
                TextColumn::make('displayName')
                    ->label(__('Display name')),
                TextColumn::make('status')
                    ->label(__('Status'))
                    ->badge(),
                TextColumn::make('createdAt')
                    ->label(__('Created at')),
            ])
            ->recordActions([
                $page->manageAction(),
                $page->approveAction(),
                $page->revokeAction(),
                $page->deleteAction(),
            ]);
    }
}
