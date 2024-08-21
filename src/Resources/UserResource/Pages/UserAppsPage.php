<?php

namespace NinjaPortal\Admin\Resources\UserResource\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use NinjaPortal\Admin\Resources\UserResource;
use NinjaPortal\Portal\Services\ApiProductService;
use NinjaPortal\Portal\Services\UserAppCredentialService;
use NinjaPortal\Portal\Services\UserAppService;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class UserAppsPage extends Page implements HasForms, HasActions
{

    const APPROVED_STATUS = 'approved';
    const REVOKED_STATUS = 'revoked';

    use InteractsWithRecord;

    protected $listeners = [
        'refreshComponent' => '$refresh'
    ];


    public Collection|array $apis;

    public array $apps = [];

    public array $app = [];

    protected static string $view = 'ninjaadmin::pages.user-apps';

    public static string $resource = UserResource::class;

    protected UserAppService $userAppService;
    protected UserAppCredentialService $userAppCredentialService;

    public function mount(int|string $record): void
    {
        $this->apis = collect((new ApiProductService())->apigeeProducts()) // todo: deek the services
            ->mapWithKeys(fn($p) => [$p->getName() => $p->getName()]);
        $this->record = $this->resolveRecord($record);
        $this->userAppService = new UserAppService($this->record->email);
        $this->apps = collect($this->userAppService->all())->toArray();
        $this->apps = Arr::map($this->apps, function ($app) {
            $app['createdAt'] = Carbon::createFromTimestampMs($app['createdAt'])->format("Y-m-d H:i:s");
            $app['credentials'] = Arr::map($app['credentials'], function ($credential) {
                $credential['apiProducts'] = Arr::map($credential['apiProducts'], function ($apiProduct) {
                    $apiProduct['old'] = true;
                    return $apiProduct;
                });
                return $credential;
            });
            return $app;
        });

    }


    public function manageAppAction(): Action
    {
        return Action::make('manageApp')
            ->label(__('Manage App'))
            ->form($this->manageAppForm())
            ->icon("heroicon-o-cog")
            ->link()
            ->fillForm(fn($arguments) => $arguments['app'])
            ->slideOver()
            ->modalWidth(MaxWidth::FiveExtraLarge)
            ->modalSubmitActionLabel(__('Save'))
            ->action(fn(array $arguments, $data) => $this->handleManageAppForm($arguments, $data));
    }

    public function handleManageAppForm(array $arguments, array $data)
    {
        $this->userAppService = new UserAppService($this->record->email);

        // handle app details
        $app = $arguments['app'];

        $appData = [
            'displayName' => $data['displayName'],
            'callbackUrl' => $data['callbackUrl'],
            'description' => $data['description'] ?? '',
        ];

        $this->userAppCredentialService = $this->userAppService->credentialService($this->record->email, $app['name']);

        $credentials = collect($data['credentials']);
        $oldCredentials = collect($app['credentials']);

        // handle deleted "credentials"
        $deletedCredentials = $oldCredentials->whereNotIn('consumerKey', $credentials->pluck('consumerKey'))->pluck('consumerKey')->toArray();

        if ($deletedCredentials){
            foreach ($deletedCredentials as $consumerKey){
                $this->userAppCredentialService->delete($consumerKey);
                Log::info('Admin deleted credential: '.$consumerKey);
            }
        }


//        try {
            // handle already existing "credentials
            foreach ($credentials->whereNotNull('consumerKey') as $credential) {
                $oldCredential = $oldCredentials->firstWhere('consumerKey', $credential['consumerKey']);

                // handle apiProduct deletions
                $deletedApiProducts = collect($oldCredential['apiProducts'])->whereNotIn('apiproduct', collect($credential['apiProducts'])->pluck('apiproduct'))->pluck('apiproduct')->toArray();


                if ($deletedApiProducts){
                    foreach ($deletedApiProducts as $apiProduct){
                        $this->userAppCredentialService->removeProducts($credential['consumerKey'], $apiProduct);
                    }
                }



                // handle credential statuses, if changed
                if ($credential['status'] !== $oldCredential['status']) {
                    $action = rtrim($credential['status'],'d'); // remove the last d from approved, revoked to match the method name
                    $this->userAppCredentialService->{$action}($oldCredential['consumerKey']);
                }

                // handle api products
                $apiProducts = $credential['apiProducts'];
                $oldApiProducts = collect($oldCredential['apiProducts']);
                foreach ($apiProducts as $apiProduct) {
                    // handle api product statuses, if changed
                    $oldApiProduct = $oldApiProducts->firstWhere('apiproduct', $apiProduct['apiproduct']);
                    if ($oldApiProduct && $apiProduct['status'] !== $oldApiProduct['status']) {
                        $action = rtrim($apiProduct['status'],'d'); // remove the last d from approved, revoked to match the method name
                        $this->userAppCredentialService->{$action.'ApiProduct'}($oldCredential['consumerKey'], $apiProduct['apiproduct']);
                    }
                }

                // handle new api products
                $newApiProducts = collect($apiProducts)->where('old','!=',true)->pluck('apiproduct')->toArray();
                if (count($newApiProducts)) {
                    try {
                        $this->userAppCredentialService->addProducts($oldCredential['consumerKey'], $newApiProducts);
                    } catch (ExceptionInterface $e) {
                        $this->addError('error', $e->getMessage());
                    }
                }
            }

            // handle new "credentials"
            foreach ($credentials->whereNull('consumerKey') as $credential) {
                $apps = collect($credential['apiProducts'])->pluck('apiproduct')->toArray();
                $this->userAppCredentialService->create($apps);
            }

            // update "app" details
            $this->userAppService->update($app['name'], $appData);
//        }catch (\Exception $e){
//            $this->addError('error', $e->getMessage());
//        }
        $this->mount($this->record->id);
    }

    public function manageAppForm(): array
    {
        return [
            Section::make('App Details')
                ->icon('heroicon-o-information-circle')
                ->headerActions([
                    FormAction::make('status')
                        ->icon(fn($get) => $get('status') == self::APPROVED_STATUS ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                        ->color(fn($get) => $get('status') == self::APPROVED_STATUS ? 'success' : 'danger')
                        ->label(fn($get) => $get('status') == self::APPROVED_STATUS ? __('Approved') : __('Revoked'))
                        ->tooltip(fn($get) => $get('status') == self::APPROVED_STATUS ? __('Revoke') : __('Approve'))
                ])->schema([
                    TextInput::make('name')
                        ->label(__('Name'))
                        ->disabled(fn($get) => $get('name') !== null)
                        ->hint(__('Name/ID of the app'))
                        ->required(),
                    TextInput::make('displayName')
                        ->label(__('Display Name'))
                        ->formatStateUsing(fn($get) => $get('displayName') ?? $get('name'))
                        ->hint(fn($get) =>  !$get('displayName') ? $get('name') : NULL)
                        ->required(),
                    TextInput::make('callbackUrl')
                        ->hint(__('A callback URL is required only for 3-legged OAuth.'))
                        ->label(__('Callback URL'))->url(),
                    Textarea::make('description')
                        ->label(__('Description')),
            ])->columns(1),

            Section::make('Credentials')->schema([
                Repeater::make('credentials')
                    ->hiddenLabel()
                    ->schema([
                        Placeholder::make('issuedAt')
                            ->content(fn($get) => Carbon::createFromTimestampMs($get('issuedAt') ?? now()->getTimestampMs())
                                ->format("M d Y g:i A"))
                            ->columnSpan(1),
                        Grid::make()->schema([
                            Toggle::make('neverExpires')
                                ->label(__('Never Expires'))
                                ->live(onBlur: true)
                                ->inline(false)
                                ->columnSpan(1),
                            DatePicker::make('expiresAt')
                                ->label(__('Expires At'))
                                ->hidden(fn($get) => $get('neverExpires') == true)
                                ->default(now()->addDay())
                                ->minDate(now())
                                ->columnSpan(1),
                        ])->columnSpan(3)->hidden(fn ($get) => $get('consumerKey') != null),
                        Placeholder::make('expiresAtLabel')
                            ->label(__('Expires At'))
                            ->hidden(fn($get) => $get('consumerKey') == null)
                            ->content(fn($get) => is_null($get('expiresAt'))
                                ? 'Never'
                                : Carbon::createFromTimestampMs($get('issuedAt'))
                                    ->format("M d Y g:i A"))
                            ->columnSpan(1),
                        ToggleButtons::make('status')
                            ->inline()
                            ->icons([
                                self::APPROVED_STATUS => 'heroicon-o-check-circle',
                                self::REVOKED_STATUS => 'heroicon-o-x-circle',
                            ])
                            ->options([
                                self::APPROVED_STATUS => __('Approve'),
                                self::REVOKED_STATUS => __('Revoke'),
                            ])->colors([
                                self::APPROVED_STATUS => 'success',
                                self::REVOKED_STATUS => 'danger',
                            ])->columnSpan(2),
                        TextInput::make('consumerKey')
                            ->label(__('Consumer Key'))
                            ->hidden(fn($get) => $get('consumerKey') == null)
                            ->password()
                            ->revealable()
                            ->readOnly()
                            ->required()
                            ->columnSpan(2),
                        TextInput::make('consumerSecret')
                            ->label(__('Consumer Secret'))
                            ->password()
                            ->hidden(fn($get) => $get('consumerSecret') == null)
                            ->revealable()
                            ->readOnly()
                            ->required()
                            ->columnSpan(2),
                        Repeater::make('apiProducts')
                            ->minItems(1)
                            ->schema([
                            Select::make('apiproduct')
                                ->label(__('API Product'))
                                ->selectablePlaceholder(false)
                                ->options(fn($get) => $get('old')
                                    ? [$get('apiproduct')]
                                    : [null => __('Select API Product'), ...$this->apis->toArray()])
                                ->required()
                                ->hint(fn($get) => $get('old') ? NULL : __('Approved'))
                                ->hintColor('success')
//                                ->hint(fn($get) => $get('old') ? NULL : __('Pending'))
//                                ->hintColor('info')
                                ->columnSpan(fn($get) => $get('old') ? 3 : 5),
                            // status
                            ToggleButtons::make('status')
                                ->inline()
                                ->default(self::APPROVED_STATUS)
                                ->hidden(fn($get) => !$get('old'))
                                ->disabled(fn($get) => !$get('old'))
                                ->icons([
                                    self::APPROVED_STATUS => 'heroicon-o-check-circle',
                                    self::REVOKED_STATUS => 'heroicon-o-x-circle',
                                ])
                                ->options([
                                    self::APPROVED_STATUS => __('Approve'),
                                    self::REVOKED_STATUS => __('Revoke'),
                                ])->colors([
                                    self::APPROVED_STATUS => 'success',
                                    self::REVOKED_STATUS => 'danger',
                                ])->required()->columnSpan(2),
                        ])->columnSpan(5)->columns(5),
                    ])->columns(4),
            ])->icon('heroicon-o-key'),
        ];
    }

    private function handleDeleteCredential(array $item,$appName): void
    {
        $this->userAppService = new UserAppService($this->record->email);
        $this->userAppCredentialService = $this->userAppService->credentialService($this->record->email, $appName);
        $this->userAppCredentialService->delete($item['consumerKey']);
        $this->mount($this->record->id);
    }


}
