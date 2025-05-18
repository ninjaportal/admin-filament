<?php

namespace NinjaPortal\Admin\Resources\UserResource\Pages;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Components\{Action as FormAction, DatePicker, Grid, Placeholder, Repeater, Section, Select, Textarea, TextInput, Toggle, ToggleButtons};
use Filament\Forms\Contracts\HasForms;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\{Arr, Collection};
use Illuminate\Support\Facades\Log;
use NinjaPortal\Admin\Resources\UserResource;
use NinjaPortal\Portal\Services\{ApiProductService, UserAppCredentialService, UserAppService};
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class UserAppsPage extends Page implements HasForms, HasActions
{
    const APPROVED_STATUS = 'approved';
    const REVOKED_STATUS = 'revoked';

    use InteractsWithRecord;

    protected $listeners = ['refreshComponent' => '$refresh'];

    public Collection|array $apis;

    public array $apps = [];

    public array $app = [];

    protected static string $view = 'ninjaadmin::pages.user-apps';

    public static string $resource = UserResource::class;

    /**
     * Retrieve the current admin's email.
     *
     * @return string|null
     */
    protected function getAdminEmail(): ?string
    {
        return auth()->user()->email; // Adjust this based on your authentication guard
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->loadApiProducts();
        $this->loadUserApps();
    }

    /**
     * Load available API products.
     */
    protected function loadApiProducts(): void
    {
        $this->apis = collect((new ApiProductService())->apigeeProducts())
            ->mapWithKeys(fn($p) => [$p->getName() => $p->getName()]);
    }

    /**
     * Load and format apps for the user.
     */
    protected function loadUserApps(): void
    {
        $this->apps = $this->formatApps((new UserAppService())->all($this->record->email));
    }

    /**
     * Format apps and their credentials for display.
     *
     * @param array $apps
     * @return array
     */
    protected function formatApps(array $apps): array
    {
        return Arr::map($apps, function ($app) {
            $app = $app->toArray();
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

    /**
     * Handle app management form submission.
     *
     * @param array $arguments
     * @param array $data
     */
    public function handleManageAppForm(array $arguments, array $data): void
    {
        $adminEmail = $this->getAdminEmail();
        $email = $this->record->email;
        $app = $arguments['app'];

        $appData = [
            'displayName' => $data['displayName'],
            'callbackUrl' => $data['callbackUrl'],
            'description' => $data['description'] ?? '',
        ];

        try {
            $credentials = collect($data['credentials']);
            $oldCredentials = collect($app['credentials']);

            // Handle deleted credentials
            $this->handleDeletedCredentials($email, $app['name'], $oldCredentials, $credentials, $adminEmail);

            // Handle updated credentials and products
            $this->handleExistingCredentials($email, $app['name'], $credentials, $oldCredentials, $adminEmail);

            // Handle new credentials
            $this->handleNewCredentials($email, $app['name'], $credentials, $adminEmail);

            // Update app details
            (new UserAppService())->update($email, $app['name'], $appData);

            Log::info('App Updated', [
                'action' => 'App Updated',
                'admin' => $adminEmail,
                'user' => $email,
                'app' => $app['name']
            ]);
        } catch (\Exception $e) {
            Log::error('App Update Failed', [
                'action' => 'App Update Failed',
                'admin' => $adminEmail,
                'user' => $email,
                'app' => $app['name'],
                'error' => $e->getMessage()
            ]);
            $this->addError('error', $e->getMessage());
        }

        // Refresh component
        $this->mount($this->record->id);
    }

    /**
     * Handle deletion of credentials.
     *
     * @param string $email
     * @param string $appName
     * @param Collection $oldCredentials
     * @param Collection $newCredentials
     * @param string $adminEmail
     */
    private function handleDeletedCredentials(string $email, string $appName, Collection $oldCredentials, Collection $newCredentials, string $adminEmail): void
    {
        $deletedCredentials = $oldCredentials->whereNotIn('consumerKey', $newCredentials->pluck('consumerKey'))->pluck('consumerKey')->toArray();

        if (!empty($deletedCredentials)) {
            $credentialService = new UserAppCredentialService();
            foreach ($deletedCredentials as $consumerKey) {
                $credentialService->delete($email, $appName, $consumerKey);
                Log::info('Credential Deleted', [
                    'action' => 'Credential Deleted',
                    'admin' => $adminEmail,
                    'user' => $email,
                    'app' => $appName,
                    'consumerKey' => $consumerKey
                ]);
            }
        }
    }

    /**
     * Handle updating existing credentials and API products.
     *
     * @param string $email
     * @param string $appName
     * @param Collection $credentials
     * @param Collection $oldCredentials
     * @param string $adminEmail
     */
    private function handleExistingCredentials(string $email, string $appName, Collection $credentials, Collection $oldCredentials, string $adminEmail): void
    {
        $credentialService = new UserAppCredentialService();

        foreach ($credentials->whereNotNull('consumerKey') as $credential) {
            $oldCredential = $oldCredentials->firstWhere('consumerKey', $credential['consumerKey']);

            // Handle API product deletions
            $deletedApiProducts = collect($oldCredential['apiProducts'])
                ->whereNotIn('apiproduct', collect($credential['apiProducts'])->pluck('apiproduct'))
                ->pluck('apiproduct')->toArray();

            foreach ($deletedApiProducts as $apiProduct) {
                $credentialService->removeProducts($email, $appName, $credential['consumerKey'], $apiProduct);
                Log::info('API Product Removed', [
                    'action' => 'API Product Removed',
                    'admin' => $adminEmail,
                    'user' => $email,
                    'app' => $appName,
                    'consumerKey' => $credential['consumerKey'],
                    'apiProduct' => $apiProduct
                ]);
            }

            // Handle credential status changes
            if ($credential['status'] !== $oldCredential['status']) {
                $action = rtrim($credential['status'], 'd');
                $credentialService->{$action}($email, $appName, $oldCredential['consumerKey']);
                Log::info('Credential Status Changed', [
                    'action' => 'Credential Status Changed',
                    'admin' => $adminEmail,
                    'user' => $email,
                    'app' => $appName,
                    'consumerKey' => $oldCredential['consumerKey'],
                    'status' => $credential['status']
                ]);
            }

            // Handle API product status changes
            $this->handleApiProductStatusChanges($email, $appName, $credentialService, $credential, $oldCredential, $adminEmail);
        }
    }

    /**
     * Handle API product status changes.
     *
     * @param string $email
     * @param string $appName
     * @param UserAppCredentialService $credentialService
     * @param array $credential
     * @param array $oldCredential
     * @param string $adminEmail
     */
    private function handleApiProductStatusChanges(string $email, string $appName, UserAppCredentialService $credentialService, array $credential, array $oldCredential, string $adminEmail): void
    {
        $apiProducts = $credential['apiProducts'];
        $oldApiProducts = collect($oldCredential['apiProducts']);

        foreach ($apiProducts as $apiProduct) {
            $oldApiProduct = $oldApiProducts->firstWhere('apiproduct', $apiProduct['apiproduct']);

            if ($oldApiProduct && $apiProduct['status'] !== $oldApiProduct['status']) {
                $action = rtrim($apiProduct['status'], 'd');
                $credentialService->{$action . 'ApiProduct'}($email, $appName, $oldCredential['consumerKey'], $apiProduct['apiproduct']);
                Log::info('API Product Status Changed', [
                    'action' => 'API Product Status Changed',
                    'admin' => $adminEmail,
                    'user' => $email,
                    'app' => $appName,
                    'consumerKey' => $oldCredential['consumerKey'],
                    'apiProduct' => $apiProduct['apiproduct'],
                    'status' => $apiProduct['status']
                ]);
            }
        }
    }

    /**
     * Handle creation of new credentials.
     *
     * @param string $email
     * @param string $appName
     * @param Collection $credentials
     * @param string $adminEmail
     */
    private function handleNewCredentials(string $email, string $appName, Collection $credentials, string $adminEmail): void
    {
        $credentialService = new UserAppCredentialService();

        foreach ($credentials->whereNull('consumerKey') as $credential) {
            $apiProducts = collect($credential['apiProducts'])->pluck('apiproduct')->toArray();
            $credentialService->create($email, $appName, $apiProducts);
            Log::info('New Credential Created', [
                'action' => 'New Credential Created',
                'admin' => $adminEmail,
                'user' => $email,
                'app' => $appName,
                'apiProducts' => $apiProducts
            ]);
        }
    }

    public function manageAppForm(): array
    {
        return [
            Section::make('App Details')
                ->icon('heroicon-o-information-circle')
                ->headerActions([
                    \Filament\Forms\Components\Actions\Action::make('status')
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

    private function handleDeleteCredential(array $item, string $appName): void
    {
        $adminEmail = $this->getAdminEmail();
        (new UserAppCredentialService())->delete($this->record->email, $appName, $item['consumerKey']);
        Log::info('Credential Deleted via Form', [
            'action' => 'Credential Deleted via Form',
            'admin' => $adminEmail,
            'user' => $this->record->email,
            'app' => $appName,
            'consumerKey' => $item['consumerKey']
        ]);
        $this->mount($this->record->id);
    }
}
