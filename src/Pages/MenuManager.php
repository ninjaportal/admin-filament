<?php

namespace NinjaPortal\Admin\Pages;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use NinjaPortal\Portal\Models\Menu;

class MenuManager extends Page
{

    use InteractsWithForms, InteractsWithActions;

    protected static ?string $title = 'Menu Manager';
    protected static ?string $navigationIcon = 'heroicon-o-bars-3';
    protected static string $view = "ninjaadmin::pages.menu-manager";

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
                ->form([
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

    public function menuForm(Form $form): Form
    {
        return $form->schema([
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


    public function linksForm(Form $form): Form
    {
        return $form->schema([
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

}
