<?php

namespace NinjaPortal\Admin\Resources\Menu\RelationManagers;

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
use Illuminate\Support\Facades\Route;
use NinjaPortal\FilamentTranslations\Resources\RelationManagers\Concerns\Translatable;
use NinjaPortal\FilamentTranslations\Tables\Actions\LocaleSwitcher;

class MenuItemsRelationManager extends RelationManager
{

    use Translatable;

    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                TextInput::make('title')
                    ->label(__("Title"))
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn ($get, $set) => $set('slug', str($get('title'))->slug()))
                    ->required(),

                TextInput::make('slug')
                    ->label(__("Slug"))
                    ->required(),

                TextInput::make('url')
                    ->url()
                    ->live(onBlur: true)
                    ->disabled(fn ($get) => !empty($get('route')))
                    ->label(__("URL")),

                Select::make('route')
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
                TextColumn::make('title'),
                TextColumn::make('url'),
                TextColumn::make('route'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                LocaleSwitcher::make(),
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
