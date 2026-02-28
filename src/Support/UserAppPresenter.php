<?php

namespace NinjaPortal\Admin\Support;

use Carbon\CarbonInterface;
use DateTimeInterface;

class UserAppPresenter
{
    public static function app(mixed $app): array
    {
        $credentials = collect(static::value($app, ['getCredentials', 'credentials'], []))
            ->map(fn ($credential) => static::credential($credential))
            ->values()
            ->all();

        $apiProducts = collect(static::value($app, ['getApiProducts', 'apiProducts'], []))
            ->map(fn ($apiProduct) => static::productName($apiProduct))
            ->filter()
            ->values()
            ->all();

        if ($apiProducts === []) {
            $apiProducts = collect($credentials)
                ->flatMap(fn (array $credential) => collect($credential['apiProducts'] ?? [])->pluck('apiproduct'))
                ->filter()
                ->unique()
                ->values()
                ->all();
        }

        return [
            'name' => static::string($app, ['getName', 'name']),
            'displayName' => static::string($app, ['getDisplayName', 'displayName']) ?: static::string($app, ['getName', 'name']),
            'callbackUrl' => static::string($app, ['getCallbackUrl', 'callbackUrl']),
            'description' => static::string($app, ['getDescription', 'description']),
            'status' => strtolower((string) static::value($app, ['getStatus', 'status'], 'approved')),
            'createdAt' => static::date(static::value($app, ['getCreatedAt', 'createdAt'])),
            'apiProducts' => $apiProducts,
            'credentials' => $credentials,
        ];
    }

    public static function credential(mixed $credential): array
    {
        $apiProducts = collect(static::value($credential, ['getApiProducts', 'apiProducts'], []))
            ->map(fn ($apiProduct) => static::product($apiProduct))
            ->values()
            ->all();

        return [
            'consumerKey' => static::string($credential, ['getConsumerKey', 'consumerKey']),
            'consumerSecret' => static::string($credential, ['getConsumerSecret', 'consumerSecret']),
            'status' => strtolower((string) static::value($credential, ['getStatus', 'status'], 'approved')),
            'expiresAt' => static::date(static::value($credential, ['getExpiresAt', 'expiresAt'])),
            'issuedAt' => static::date(static::value($credential, ['getIssuedAt', 'issuedAt'])),
            'apiProducts' => $apiProducts,
        ];
    }

    public static function product(mixed $apiProduct): array
    {
        $name = static::productName($apiProduct);

        return [
            'apiproduct' => $name,
            'status' => strtolower((string) static::value($apiProduct, ['getStatus', 'status'], 'approved')),
        ];
    }

    protected static function productName(mixed $apiProduct): ?string
    {
        if (is_string($apiProduct) && $apiProduct !== '') {
            return $apiProduct;
        }

        return static::string($apiProduct, ['getApiproduct', 'getApiProduct', 'apiproduct', 'apiProduct']);
    }

    protected static function string(mixed $value, array $candidates): ?string
    {
        $resolved = static::value($value, $candidates);

        if ($resolved === null) {
            return null;
        }

        return is_string($resolved) ? $resolved : (string) $resolved;
    }

    protected static function value(mixed $subject, array $candidates, mixed $default = null): mixed
    {
        foreach ($candidates as $candidate) {
            if (is_object($subject) && method_exists($subject, $candidate)) {
                return $subject->{$candidate}();
            }

            if (is_object($subject) && isset($subject->{$candidate})) {
                return $subject->{$candidate};
            }

            if (is_array($subject) && array_key_exists($candidate, $subject)) {
                return $subject[$candidate];
            }
        }

        return $default;
    }

    protected static function date(mixed $value): ?string
    {
        if ($value instanceof CarbonInterface) {
            return $value->toDateTimeString();
        }

        if ($value instanceof DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_numeric($value)) {
            return date('Y-m-d H:i:s', (int) $value / (strlen((string) $value) > 10 ? 1000 : 1));
        }

        return is_string($value) && $value !== '' ? $value : null;
    }
}
