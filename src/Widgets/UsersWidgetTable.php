<?php

namespace NinjaPortal\Admin\Widgets;

use Filament\Tables\Columns;
use Filament\Tables\Actions;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use NinjaPortal\Admin\Resources\UserResource\Pages\EditUser;
use NinjaPortal\Portal\Models\User;

class UsersWidgetTable extends TableWidget
{

    protected int | string | array $columnSpan = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(User::query())
            ->columns([
                Columns\TextColumn::make('first_name')->label('First Name'),
                Columns\TextColumn::make('last_name')->label('Last Name'),
                Columns\TextColumn::make('email')->label('Email'),
                Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        User::$ACTIVE_STATUS => 'success',
                        User::$INACTIVE_STATUS => 'danger',
                        default => 'gray',
                    }),
                Columns\TextColumn::make('created_at')->label('Registered At')
                    ->getStateUsing(fn ($record) => $record->created_at->format('Y-m-d H:i:s')),
            ])->actions([
                Actions\ViewAction::make()->url(fn ($record) => EditUser::getUrl(["record"=> $record->id ])),
            ]);
    }



}
