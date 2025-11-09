<?php

namespace NinjaPortal\Admin\Pages;

use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use NinjaPortal\Admin\Constants;
use NinjaPortal\FilamentShield\Traits\HasPageShield;
use NinjaPortal\Portal\Models\Menu;

class MenuManager extends Page
{

    use InteractsWithForms, InteractsWithActions, HasPageShield;

    protected static ?string $title = 'Menu Manager';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-bars-3';
    protected string $view = "ninjaadmin::pages.menu-manager";

    public ?array $links = [];
    public ?array $newLink = [];
    public ?array $menuData = [
        'currentMenu' => null,
        'content' => []
    ];

    public ?Menu $selectedMenu;


    protected function getActions(): array
    {
        return [
            CreateAction::make()
                ->model(Menu::class)
                ->createAnother(false)
                ->label('New Menu')
                ->schema([
                    Section::make()->schema([
                        TextInput::make('name')
                            ->label('Name')
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Set $set, ?string $state) => $set('slug', Str::slug($state)))
                            ->required(),
                        TextInput::make('slug')
                            ->label('Slug')
                            ->required(),
                    ])->columns(2)
                ]),
        ];
    }

    protected function getForms(): array
    {
        return [
            'linksForm',
            'menuForm'
        ];
    }


    // forms

    public function menuForm(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()->schema([
                Select::make('currentMenu')
                    ->live(onBlur: true)
                    ->label('Menu')
                    ->options(fn() => Menu::pluck('name', 'id')->toArray())
                    ->selectablePlaceholder(false)
                    ->native(false)
                    ->afterStateUpdated(fn(Set $set, $state) => $this->setSelectMenu($state))
                    ->required(),
            ])
        ])->statePath('menuData');
    }


    public function linksForm(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->label('Name')->required(),
            TextInput::make('url')->label('URL')->required(),
        ])->statePath('newLink');
    }



    // handlers

    public function addLink()
    {
        if (!isset($this->menuData['currentMenu'])) {
            Notification::make()
                ->title('Please select a menu first')
                ->danger()->send();
            return;
        }
        $this->newLink['slug'] = Str::slug($this->newLink['name']);
        $this->menuData['content'][$this->newLink['slug']] = $this->newLink;
        $this->newLink = [];
    }

    public function setSelectMenu(int $id)
    {
        $this->selectedMenu = Menu::find($id);
        $this->menuData['content'] = $this->selectedMenu->content ?? [];
    }


    public function reorderMenuItems(array $slugs)
    {
        $newMenu = Arr::sort($this->menuData['content'], function ($value, $key) use ($slugs) {
            return array_search($key, $slugs);
        });
        $this->menuData['content'] = $newMenu;
    }


    public function deleteMenuItem($slug)
    {
        unset($this->menuData['content'][$slug]);
    }

    public function editMenuItem($slug)
    {
        $this->newLink = $this->menuData['content'][$slug];
    }

    public function saveMenu()
    {
        if (!isset($this->menuData['currentMenu'])) {
            Notification::make()
                ->title('Please select a menu first')
                ->danger()->send();
            return;
        }
        $this->selectedMenu->content = $this->menuData['content'];
        $this->selectedMenu->save();
        Notification::make()
            ->title('Menu saved')
            ->success()->send();
    }

    public static function getNavigationGroup(): ?string
    {
        return __("ninjaadmin::ninjaadmin.navigation_groups.".Constants::NAVIGATION_GROUPS['ADMIN']);
    }
}
