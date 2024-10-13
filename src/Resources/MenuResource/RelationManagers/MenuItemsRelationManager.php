<?php

namespace NinjaPortal\Admin\Resources\MenuResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Route;
use NinjaPortal\FilamentTranslations\Resources\RelationManagers\Concerns\Translatable;
use NinjaPortal\FilamentTranslations\Tables\Actions\LocaleSwitcher;

class MenuItemsRelationManager extends RelationManager
{

    use Translatable;

    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()->schema([
                Forms\Components\TextInput::make('title')
                    ->label(__("Title"))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($get, $set) => $set('slug', str($get('title'))->slug()))
                    ->required(),

                Forms\Components\TextInput::make('slug')
                    ->label(__("Slug"))
                    ->required(),

                Forms\Components\TextInput::make('url')
                    ->url()
                    ->live(onBlur: true)
                    ->disabled(fn ($get) => !empty($get('route')))
                    ->label(__("URL")),

                Forms\Components\Select::make('route')
                    ->label(__("Route"))
                    ->searchable()
                    ->disabled(fn ($get) => !empty($get('url')))
                    ->live(onBlur: true)
                    ->options(fn () =>
                         collect(Route::getRoutes()->getRoutes())
                            ->map(function ($route) {
                                return [
                                    'uri' => $route->uri,
                                    'name' => $route->getName(),
                                    'action' => $route->getActionName(),
                                    'middleware' => $route->middleware(),
                                ];
                            })
                            ->filter(function ($route) {
                                return $route['name'] !== null || str($route['action'])->startsWith('livewire');
                            })
                            ->filter(function ($route) {
                                return in_array('web', $route['middleware']);
                            })
                            ->mapWithKeys(function ($route) {
                                return [$route['name'] => $route['name']];
                            })
                    )

            ])
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('title'),
                Tables\Columns\TextColumn::make('url'),
                Tables\Columns\TextColumn::make('route'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
                LocaleSwitcher::make(),
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
