<?php

namespace NinjaPortal\Admin\Resources\User\Pages;

use Filament\Schemas\Schema;
use Filament\Support\ArrayRecord;
use Carbon\Carbon;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\Width;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\{Arr, Collection};
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use NinjaPortal\Admin\Resources\User\Pages\Schemas\ManageUserAppSchema;
use NinjaPortal\Admin\Resources\User\Pages\Tables\UserAppsTable;
use NinjaPortal\Admin\Resources\User\UserResource;
use NinjaPortal\Portal\Services\{ApiProductService, UserAppCredentialService, UserAppService};

class UserAppsPage extends Page implements HasActions, HasTable, HasSchemas
{
    use InteractsWithRecord;
    use InteractsWithTable;
    use InteractsWithSchemas;

    const APPROVED_STATUS = 'approved';
    const REVOKED_STATUS = 'revoked';

    protected string $view = 'ninjaadmin::pages.user-apps';

    public static string $resource = UserResource::class;
    public Collection|array $apis = [];

    public array $apps = [];

    protected bool $appsLoaded = false;

    protected bool $apisLoaded = false;
    protected $listeners = ['refreshComponent' => '$refresh'];

    public function table(Table $table): Table
    {
        return UserAppsTable::configure($table, $this);
    }

    public function deleteAppAction()
    {
        return Action::make('deleteApp')
            ->label(__('Delete App'))
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(fn (Action $action) => __('Delete App ":name"?', ['name' => $action->getRecord()['displayName'] ?? '']))
            ->modalSubheading(__('Are you sure you want to delete this app? This action cannot be undone.'))
            ->action(function (Action $action, array $arguments, array $data): void {
                $record = $action->getRecord();
                $adminEmail = $this->getAdminEmail();
                $email = $this->record->email;

                try {
                    (new UserAppService())->delete($email, $record['name']);

                    Log::info('App Deleted', [
                        'action' => 'App Deleted',
                        'admin' => $adminEmail,
                        'user' => $email,
                        'app' => $record['name'],
                    ]);

                    Notification::make()
                        ->title(__('App deleted'))
                        ->success()
                        ->send();
                } catch (Exception $e) {
                    Log::error('App Deletion Failed', [
                        'action' => 'App Deletion Failed',
                        'admin' => $adminEmail,
                        'user' => $email,
                        'app' => $record['name'],
                        'error' => $e->getMessage(),
                    ]);

                    $this->addError('error', $e->getMessage());

                    Notification::make()
                        ->title(__('Unable to delete app'))
                        ->body($e->getMessage())
                        ->danger()
                        ->send();

                    return;
                }

                $this->refreshApps();
            });
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('createApp')
                ->label(__('New App'))
                ->icon('heroicon-o-plus')
                ->modalWidth(Width::FiveExtraLarge)
                ->modalSubmitActionLabel(__('Create'))
                ->mountUsing(function (Action $action, ?Schema $schema): void {
                    $this->ensureApiProductsLoaded();

                    $schema?->fill($this->getEmptyAppData());
                })
                ->schema(ManageUserAppSchema::make(true))
                ->action(function (array $data): void {
                    $this->handleCreateApp($data);
                }),
        ];
    }

    public function getAppTableRecords(): array
    {
        if (! $this->appsLoaded) {
            $this->loadUserApps();
        }

        return $this->apps;
    }

    public function manageAppAction(): Action
    {
        return Action::make('manageApp')
            ->label(__('Manage App'))
            ->schema(ManageUserAppSchema::make())
            ->icon("heroicon-o-cog")
            ->link()
            ->slideOver()
            ->modalWidth(Width::FiveExtraLarge)
            ->modalSubmitActionLabel(__('Save'))
            ->mountUsing(function (Action $action, ?Schema $schema): void {
                $this->ensureApiProductsLoaded();

                $record = $action->getRecord();
                $schema?->fill(array_merge($this->getEmptyAppData(), is_array($record) ? $record : []));
            })
            ->action(function (Action $action, array $arguments, array $data): void {
                $record = $action->getRecord();
                $this->handleUpdateApp(is_array($record) ? $record : $this->getEmptyAppData(), $data);
            });
    }

    protected function getContentProperty(): Htmlable
    {
        return $this->table;
    }

    /**
     * Handle app management form submission.
     */
    private function handleCreateApp(array $data): void
    {
        $adminEmail = $this->getAdminEmail();
        $email = $this->record->email;

        $error = $this->validateAppPayload($data);
        if ($error !== null) {
            $this->addError('error', $error);

            Notification::make()
                ->title(__('Unable to create app'))
                ->body($error)
                ->danger()
                ->send();

            return;
        }

        $appData = [
            'displayName' => $data['displayName'],
            'callbackUrl' => $data['callbackUrl'],
            'description' => $data['description'] ?? '',
            'apiProducts' => $data['apiProducts'] ?? [],
            'status' => $data['status'] ?? self::APPROVED_STATUS,
        ];

        try {
            (new UserAppService())->create($email, [
                'name' => $data['name'],
                ...$appData,
            ]);

            Log::info('App Created', [
                'action' => 'App Created',
                'admin' => $adminEmail,
                'user' => $email,
                'app' => $data['name'],
            ]);

            Notification::make()
                ->title(__('App created'))
                ->success()
                ->send();
        } catch (Exception $e) {
            Log::error('App Creation Failed', [
                'action' => 'App Creation Failed',
                'admin' => $adminEmail,
                'user' => $email,
                'app' => $data['name'] ?? null,
                'error' => $e->getMessage(),
            ]);

            $this->addError('error', $e->getMessage());

            Notification::make()
                ->title(__('Unable to create app'))
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        $this->refreshApps();
    }

    private function handleUpdateApp(array $app, array $data): void
    {
        $adminEmail = $this->getAdminEmail();
        $email = $this->record->email;

        $error = $this->validateAppPayload($data);
        if ($error !== null) {
            $this->addError('error', $error);

            Notification::make()
                ->title(__('Unable to update app'))
                ->body($error)
                ->danger()
                ->send();

            return;
        }

        $appData = [
            'displayName' => $data['displayName'],
            'callbackUrl' => $data['callbackUrl'],
            'description' => $data['description'] ?? '',
            'apiProducts' => $data['apiProducts'] ?? [],
            'status' => $data['status'] ?? ($app['status'] ?? self::APPROVED_STATUS),
        ];

        try {
            $credentials = collect($data['credentials'] ?? []);
            $oldCredentials = collect($app['credentials'] ?? []);

            $this->handleDeletedCredentials($email, $app['name'], $oldCredentials, $credentials, $adminEmail);
            $this->handleExistingCredentials($email, $app['name'], $credentials, $oldCredentials, $adminEmail);
            $this->handleNewCredentials($email, $app['name'], $credentials, $adminEmail);

            (new UserAppService())->update($email, $app['name'], $appData);

            // if the app status has changed from the previous status, use the methods approve and revoke
            if (isset($app['status']) && $appData['status'] !== $app['status']) {
                $appService = new UserAppService();
                if ($appData['status'] === self::APPROVED_STATUS) {
                    $appService->approve($email, $app['name']);
                } elseif ($appData['status'] === self::REVOKED_STATUS) {
                    $appService->revoke($email, $app['name']);
                }
            }
            Log::info('App Updated', [
                'action' => 'App Updated',
                'admin' => $adminEmail,
                'user' => $email,
                'app' => $app['name'],
            ]);

            Notification::make()
                ->title(__('App saved'))
                ->success()
                ->send();
        } catch (Exception $e) {
            Log::error('App Update Failed', [
                'action' => 'App Update Failed',
                'admin' => $adminEmail,
                'user' => $email,
                'app' => $app['name'],
                'error' => $e->getMessage(),
            ]);

            $this->addError('error', $e->getMessage());

            Notification::make()
                ->title(__('Unable to update app'))
                ->body($e->getMessage())
                ->danger()
                ->send();

            return;
        }

        $this->refreshApps();
    }

    private function validateAppPayload(array $data): ?string
    {
        $validator = Validator::make(
            [
                'name' => $data['name'] ?? null,
                'displayName' => $data['displayName'] ?? null,
            ],
            [
                'name' => ['required', 'string'],
                'displayName' => ['required', 'string'],
            ]
        );

        return $validator->fails() ? $validator->errors()->first() : null;
    }

    /**
     * Retrieve the current admin's email.
     *
     * @return string|null
     */
    protected function getAdminEmail(): ?string
    {
        return auth()->user()->email; // Adjust this based on your authentication guard
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

            if ($oldCredential === null) {
                continue;
            }

            // Handle API product deletions
            $deletedApiProducts = collect($oldCredential['apiProducts'] ?? [])
                ->whereNotIn('apiproduct', collect($credential['apiProducts'] ?? [])->pluck('apiproduct'))
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
            $newCredentialStatus = $credential['status'] ?? $oldCredential['status'] ?? null;
            if ($newCredentialStatus && $newCredentialStatus !== ($oldCredential['status'] ?? null)) {
                $action = rtrim($newCredentialStatus, 'd');
                if (method_exists($credentialService, $action)) {
                    $credentialService->{$action}($email, $appName, $oldCredential['consumerKey']);
                }
                Log::info('Credential Status Changed', [
                    'action' => 'Credential Status Changed',
                    'admin' => $adminEmail,
                    'user' => $email,
                    'app' => $appName,
                    'consumerKey' => $oldCredential['consumerKey'],
                    'status' => $newCredentialStatus,
                ]);
            }

            $this->handleAddedApiProducts($email, $appName, $credential['consumerKey'], $credential, $oldCredential, $credentialService, $adminEmail);
            $this->handleApiProductStatusChanges($email, $appName, $credentialService, $credential, $oldCredential, $adminEmail);
        }
    }

    private function handleAddedApiProducts(
        string $email,
        string $appName,
        string $consumerKey,
        array $credential,
        array $oldCredential,
        UserAppCredentialService $credentialService,
        string $adminEmail
    ): void {
        $oldProducts = collect($oldCredential['apiProducts'] ?? [])->pluck('apiproduct')->filter()->values();
        $newProducts = collect($credential['apiProducts'] ?? [])->pluck('apiproduct')->filter()->values();

        $addedProducts = $newProducts->diff($oldProducts);

        if ($addedProducts->isEmpty()) {
            return;
        }

        $credentialService->addProducts($email, $appName, $consumerKey, $addedProducts->all());

        Log::info('API Products Added', [
            'action' => 'API Products Added',
            'admin' => $adminEmail,
            'user' => $email,
            'app' => $appName,
            'consumerKey' => $consumerKey,
            'apiProducts' => $addedProducts->all(),
        ]);
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
        $apiProducts = $credential['apiProducts'] ?? [];
        $oldApiProducts = collect($oldCredential['apiProducts'] ?? []);

        foreach ($apiProducts as $apiProduct) {
            $oldApiProduct = $oldApiProducts->firstWhere('apiproduct', $apiProduct['apiproduct']);

            if ($oldApiProduct && ($apiProduct['status'] ?? null) !== ($oldApiProduct['status'] ?? null)) {
                $action = rtrim($apiProduct['status'], 'd');
                $method = $action . 'ApiProduct';
                if (method_exists($credentialService, $method)) {
                    $credentialService->{$method}($email, $appName, $oldCredential['consumerKey'], $apiProduct['apiproduct']);
                }
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
            $approvedProducts = collect($credential['apiProducts'] ?? [])
                ->filter(fn ($product) => ($product['status'] ?? self::APPROVED_STATUS) === self::APPROVED_STATUS)
                ->pluck('apiproduct')
                ->filter()
                ->toArray();

            $credentialService->create($email, $appName, $approvedProducts);
            Log::info('New Credential Created', [
                'action' => 'New Credential Created',
                'admin' => $adminEmail,
                'user' => $email,
                'app' => $appName,
                'apiProducts' => $approvedProducts
            ]);
        }
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->appsLoaded = false;
        $this->apisLoaded = false;
    }

    /**
     * Load available API products.
     */
    public function ensureApiProductsLoaded(): void
    {
        if ($this->apisLoaded) {
            return;
        }

        try {
            $this->apis = Cache::remember('user-apps.api-products', now()->addMinutes(10), function () {
                return collect((new ApiProductService())->apigeeProducts() ?? [])
                    ->mapWithKeys(fn($p) => [$p->getName() => $p->getName()]);
            });
        } catch (Exception $e) {
            Log::error('Failed to load API products', [
                'action' => 'Load API Products',
                'user' => $this->record->email ?? null,
                'error' => $e->getMessage(),
            ]);

            $this->apis = collect();

            Notification::make()
                ->title(__('Unable to load API products'))
                ->body(__('Please try again later.'))
                ->danger()
                ->send();
        }

        $this->apisLoaded = true;
    }

    /**
     * Load and format apps for the user.
     */
    protected function loadUserApps(): void
    {
        try {
            $this->apps = $this->formatApps((new UserAppService())->all($this->record->email));
            $this->appsLoaded = true;
        } catch (Exception $e) {
            Log::error('Failed to load user apps', [
                'action' => 'Load User Apps',
                'user' => $this->record->email,
                'error' => $e->getMessage(),
            ]);

            $this->apps = [];
            $this->addError('error', __('Unable to load apps.'));

            Notification::make()
                ->title(__('Unable to load apps'))
                ->body(__('Please try again later.'))
                ->danger()
                ->send();
        }
    }

    /**
     * Format apps and their credentials for display.
     *
     * @param array $apps
     * @return array
     */
    protected function formatApps(Collection $apps): array
    {
        return $apps->mapWithKeys(function ($app) {
            $app = $app->toArray();
            $app['createdAt'] = Carbon::createFromTimestampMs($app['createdAt'])->format("Y-m-d H:i:s");
            $app['credentials'] = Arr::map($app['credentials'], function ($credential) {
                $credential['apiProducts'] = Arr::map($credential['apiProducts'], function ($apiProduct) {
                    $apiProduct['old'] = true;
                    return $apiProduct;
                });
                return $credential;
            });
            $app['apiProducts'] = collect($app['credentials'])
                ->flatMap(fn ($credential) => collect($credential['apiProducts'])->pluck('apiproduct'))
                ->unique()
                ->values()
                ->toArray();
            $app[ArrayRecord::getKeyName()] = $app['name'];
            $app['status'] = $app['status'] ?? self::APPROVED_STATUS;
            return [$app['name'] => $app];
        })->toArray();
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
        $this->refreshApps();
    }

    protected function refreshApps(): void
    {
        $this->appsLoaded = false;
        $this->apps = [];
        $this->flushCachedTableRecords();
        $this->dispatch('refreshComponent');
    }

    protected function getEmptyAppData(): array
    {
        return [
            'name' => null,
            'displayName' => null,
            'callbackUrl' => null,
            'description' => null,
            'apiProducts' => [],
            'credentials' => [],
            'consumerKey' => null,
            'consumerSecret' => null,
            'status' => self::APPROVED_STATUS,
        ];
    }
}
