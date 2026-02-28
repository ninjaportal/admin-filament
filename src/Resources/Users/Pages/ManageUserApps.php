<?php

namespace NinjaPortal\Admin\Resources\Users\Pages;

use Exception;
use Filament\Actions\Action;
use Filament\Actions\Contracts\HasActions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use NinjaPortal\Admin\Resources\Users\Pages\Schemas\UserAppForm;
use NinjaPortal\Admin\Resources\Users\Pages\Tables\UserAppsTable;
use NinjaPortal\Admin\Resources\Users\UserResource;
use NinjaPortal\Admin\Support\UserAppPresenter;
use NinjaPortal\Portal\Contracts\Services\ApiProductServiceInterface;
use NinjaPortal\Portal\Contracts\Services\UserAppCredentialServiceInterface;
use NinjaPortal\Portal\Contracts\Services\UserAppServiceInterface;

class ManageUserApps extends Page implements HasActions, HasSchemas, HasTable
{
    use InteractsWithRecord;
    use InteractsWithSchemas;
    use InteractsWithTable;

    protected static string $resource = UserResource::class;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $apps = [];

    /**
     * @var array<string, string>
     */
    public array $apiProducts = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->loadApiProducts();
        $this->refreshApps();
    }

    public function getTitle(): string
    {
        return __('portal-admin::portal-admin.pages.user_apps').' - '.($this->record->full_name ?: $this->record->email);
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        return UserAppsTable::configure($table, $this);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label(__('Refresh'))
                ->icon('heroicon-o-arrow-path')
                ->action(function (): void {
                    $this->loadApiProducts();
                    $this->refreshApps();

                    Notification::make()
                        ->title(__('Apps refreshed.'))
                        ->success()
                        ->send();
                }),
            Action::make('createApp')
                ->label(__('Create app'))
                ->icon('heroicon-o-plus')
                ->modalWidth(Width::FiveExtraLarge)
                ->schema(UserAppForm::make($this->apiProducts, true))
                ->action(fn (array $data) => $this->handleCreateApp($data)),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getAppRecords(): array
    {
        return $this->apps;
    }

    public function manageAction(): Action
    {
        return Action::make('manage')
            ->label(__('Manage'))
            ->icon('heroicon-o-cog-6-tooth')
            ->schema(UserAppForm::make($this->apiProducts))
            ->fillForm(fn (array $record) => $record)
            ->modalWidth(Width::FiveExtraLarge)
            ->action(fn (array $data, array $record) => $this->handleUpdateApp($record, $data));
    }

    public function approveAction(): Action
    {
        return Action::make('approve')
            ->label(__('portal-admin::portal-admin.actions.approve'))
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->visible(fn (array $record) => ($record['status'] ?? 'approved') !== 'approved')
            ->action(fn (array $record) => $this->wrapAction(function () use ($record) {
                $this->apps()->approve($this->record->email, $record['name']);
                $this->refreshApps();
            }, __('App approved.')));
    }

    public function revokeAction(): Action
    {
        return Action::make('revoke')
            ->label(__('portal-admin::portal-admin.actions.revoke'))
            ->color('danger')
            ->icon('heroicon-o-no-symbol')
            ->visible(fn (array $record) => ($record['status'] ?? 'approved') !== 'revoked')
            ->action(fn (array $record) => $this->wrapAction(function () use ($record) {
                $this->apps()->revoke($this->record->email, $record['name']);
                $this->refreshApps();
            }, __('App revoked.')));
    }

    public function deleteAction(): Action
    {
        return Action::make('delete')
            ->label(__('Delete'))
            ->color('danger')
            ->icon('heroicon-o-trash')
            ->requiresConfirmation()
            ->action(fn (array $record) => $this->wrapAction(function () use ($record) {
                $this->apps()->delete($this->record->email, $record['name']);
                $this->refreshApps();
            }, __('App deleted.')));
    }

    protected function handleCreateApp(array $data): void
    {
        $this->wrapAction(function () use ($data) {
            $this->apps()->create($this->record->email, [
                'name' => (string) $data['name'],
                'displayName' => (string) ($data['displayName'] ?? $data['name']),
                'callbackUrl' => $data['callbackUrl'] ?: null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? 'approved',
                'apiProducts' => array_values(array_filter($data['apiProducts'] ?? [])),
            ]);

            $this->refreshApps();
        }, __('App created.'));
    }

    /**
     * @param  array<string, mixed>  $record
     * @param  array<string, mixed>  $data
     */
    protected function handleUpdateApp(array $record, array $data): void
    {
        $this->wrapAction(function () use ($record, $data) {
            $appName = (string) $record['name'];

            $this->apps()->update($this->record->email, $appName, [
                'name' => $appName,
                'displayName' => (string) ($data['displayName'] ?? $record['displayName'] ?? $appName),
                'callbackUrl' => $data['callbackUrl'] ?: null,
                'description' => $data['description'] ?? null,
                'status' => $data['status'] ?? ($record['status'] ?? 'approved'),
            ]);

            $this->syncCredentialChanges($appName, $record, $data);

            $targetStatus = strtolower((string) ($data['status'] ?? ($record['status'] ?? 'approved')));
            $currentStatus = strtolower((string) ($record['status'] ?? 'approved'));

            if ($targetStatus !== $currentStatus) {
                if ($targetStatus === 'approved') {
                    $this->apps()->approve($this->record->email, $appName);
                }

                if ($targetStatus === 'revoked') {
                    $this->apps()->revoke($this->record->email, $appName);
                }
            }

            $this->refreshApps();
        }, __('App updated.'));
    }

    /**
     * @param  array<string, mixed>  $record
     * @param  array<string, mixed>  $data
     */
    protected function syncCredentialChanges(string $appName, array $record, array $data): void
    {
        $existing = collect($record['credentials'] ?? [])->keyBy('consumerKey');
        $submitted = collect($data['credentials'] ?? []);

        $submittedKeys = $submitted
            ->map(fn (array $credential) => $credential['consumerKey'] ?? null)
            ->filter()
            ->values()
            ->all();

        $deletedKeys = $existing->keys()->diff($submittedKeys);

        foreach ($deletedKeys as $key) {
            $this->credentials()->delete($this->record->email, $appName, (string) $key);
        }

        foreach ($submitted as $credential) {
            $consumerKey = $credential['consumerKey'] ?? null;
            $apiProducts = collect($credential['apiProducts'] ?? [])
                ->map(fn (array $product) => (string) ($product['apiproduct'] ?? ''))
                ->filter()
                ->values()
                ->all();

            if (! $consumerKey) {
                if ($apiProducts !== []) {
                    $this->credentials()->create($this->record->email, $appName, $apiProducts);
                }

                continue;
            }

            $current = collect($existing->get($consumerKey)['apiProducts'] ?? [])->keyBy('apiproduct');
            $next = collect($credential['apiProducts'] ?? [])->keyBy('apiproduct');

            $toRemove = $current->keys()->diff($next->keys());
            foreach ($toRemove as $productName) {
                $this->credentials()->removeProducts($this->record->email, $appName, (string) $consumerKey, (string) $productName);
            }

            $toAdd = $next->keys()->diff($current->keys());
            if ($toAdd->isNotEmpty()) {
                $this->credentials()->addProducts($this->record->email, $appName, (string) $consumerKey, $toAdd->values()->all());
            }

            foreach ($next as $productName => $productPayload) {
                $targetStatus = strtolower((string) ($productPayload['status'] ?? 'approved'));
                $currentStatus = strtolower((string) ($current->get($productName)['status'] ?? 'approved'));

                if ($targetStatus === $currentStatus) {
                    continue;
                }

                if ($targetStatus === 'approved') {
                    $this->credentials()->approveApiProduct($this->record->email, $appName, (string) $consumerKey, (string) $productName);
                }

                if ($targetStatus === 'revoked') {
                    $this->credentials()->revokeApiProduct($this->record->email, $appName, (string) $consumerKey, (string) $productName);
                }
            }

            $targetCredentialStatus = strtolower((string) ($credential['status'] ?? 'approved'));
            $currentCredentialStatus = strtolower((string) ($existing->get($consumerKey)['status'] ?? 'approved'));

            if ($targetCredentialStatus === 'approved' && $currentCredentialStatus !== 'approved') {
                $this->credentials()->approve($this->record->email, $appName, (string) $consumerKey);
            }

            if ($targetCredentialStatus === 'revoked' && $currentCredentialStatus !== 'revoked') {
                $this->credentials()->revoke($this->record->email, $appName, (string) $consumerKey);
            }
        }
    }

    protected function refreshApps(): void
    {
        $this->apps = $this->apps()
            ->all($this->record->email)
            ->map(fn ($app) => UserAppPresenter::app($app))
            ->values()
            ->all();

        $this->flushCachedTableRecords();
    }

    protected function loadApiProducts(): void
    {
        try {
            $this->apiProducts = collect(app(ApiProductServiceInterface::class)->apigeeProducts())
                ->mapWithKeys(fn ($product) => [$product->getName() => $product->getName()])
                ->sortKeys()
                ->all();
        } catch (Exception $exception) {
            report($exception);
            $this->apiProducts = [];
        }
    }

    protected function wrapAction(callable $callback, string $successMessage): void
    {
        try {
            $callback();

            Notification::make()
                ->title($successMessage)
                ->success()
                ->send();
        } catch (Exception $exception) {
            report($exception);

            Notification::make()
                ->title(__('Unable to complete action.'))
                ->body($exception->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function apps(): UserAppServiceInterface
    {
        return app(UserAppServiceInterface::class);
    }

    protected function credentials(): UserAppCredentialServiceInterface
    {
        return app(UserAppCredentialServiceInterface::class);
    }
}
