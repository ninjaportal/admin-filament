<?php

namespace NinjaPortal\Admin\Support\Settings;

use Illuminate\Validation\ValidationException;
use NinjaPortal\Portal\Constants;

class SettingUi
{
    private const FORM_FIELD_MAP = [
        'string' => 'value_string',
        'integer' => 'value_integer',
        'boolean' => 'value_boolean',
        'float' => 'value_float',
        'json' => 'value_json',
    ];

    private const TYPE_LABELS = [
        'string' => 'Text',
        'integer' => 'Number',
        'boolean' => 'Toggle',
        'float' => 'Decimal',
        'json' => 'JSON',
    ];

    private const TYPE_ICONS = [
        'string' => 'heroicon-o-chat-bubble-left-right',
        'integer' => 'heroicon-o-hashtag',
        'boolean' => 'heroicon-o-bolt',
        'float' => 'heroicon-o-calculator',
        'json' => 'heroicon-o-code-bracket-square',
    ];

    private const TYPE_COLORS = [
        'string' => 'gray',
        'integer' => 'info',
        'boolean' => 'success',
        'float' => 'warning',
        'json' => 'primary',
    ];

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return collect(Constants::SETTING_TYPES)
            ->mapWithKeys(fn (string $type): array => [$type => self::TYPE_LABELS[$type] ?? ucfirst($type)])
            ->all();
    }

    public static function labelForType(?string $type): string
    {
        if (! is_string($type) || $type === '') {
            return __('Unknown');
        }

        return __(self::TYPE_LABELS[$type] ?? ucfirst($type));
    }

    public static function iconForType(?string $type): string
    {
        return self::TYPE_ICONS[$type] ?? 'heroicon-o-cog-6-tooth';
    }

    public static function colorForType(?string $type): string
    {
        return self::TYPE_COLORS[$type] ?? 'gray';
    }

    public static function groupTabKey(int|string $groupId): string
    {
        return "group-{$groupId}";
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareForForm(array $data): array
    {
        $type = self::normalizeType($data['type'] ?? null);
        $data['type'] = $type;

        foreach (self::FORM_FIELD_MAP as $field) {
            $data[$field] = null;
        }

        $value = $data['value'] ?? null;

        if ($value === null) {
            return $data;
        }

        $field = self::fieldForType($type);

        $data[$field] = match ($type) {
            'integer' => (int) $value,
            'boolean' => self::toBool($value),
            'float' => (float) $value,
            'json' => self::formatJsonForForm($value),
            default => (string) $value,
        };

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function prepareForStorage(array $data): array
    {
        $type = self::normalizeType($data['type'] ?? null);
        $field = self::fieldForType($type);
        $rawValue = $data[$field] ?? null;

        $data['type'] = $type;
        $data['value'] = match ($type) {
            'integer' => self::normalizeIntegerValue($rawValue),
            'boolean' => self::normalizeBooleanValue($rawValue),
            'float' => self::normalizeFloatValue($rawValue),
            'json' => self::normalizeJsonValue($rawValue),
            default => self::normalizeStringValue($rawValue),
        };

        foreach (self::FORM_FIELD_MAP as $mappedField) {
            unset($data[$mappedField]);
        }

        return $data;
    }

    public static function preview(?string $value, ?string $type): string
    {
        $normalizedType = self::normalizeType($type);

        if ($value === null || $value === '') {
            return __('Not set');
        }

        return match ($normalizedType) {
            'boolean' => self::toBool($value) ? __('Enabled') : __('Disabled'),
            'json' => self::summarizeJson($value),
            default => str($value)->squish()->limit(90)->toString(),
        };
    }

    private static function normalizeType(mixed $type): string
    {
        return is_string($type) && array_key_exists($type, Constants::SETTING_TYPES)
            ? $type
            : 'string';
    }

    private static function fieldForType(string $type): string
    {
        return self::FORM_FIELD_MAP[$type] ?? self::FORM_FIELD_MAP['string'];
    }

    private static function toBool(mixed $value): bool
    {
        $normalized = strtolower(trim((string) $value));

        if (in_array($normalized, ['1', 'true', 'yes', 'on'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'false', 'no', 'off', ''], true)) {
            return false;
        }

        return (bool) $normalized;
    }

    private static function formatJsonForForm(mixed $value): ?string
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $value;
        }

        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private static function normalizeStringValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private static function normalizeIntegerValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (string) (int) $value;
    }

    private static function normalizeBooleanValue(mixed $value): string
    {
        return self::toBool($value) ? '1' : '0';
    }

    private static function normalizeFloatValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = (float) $value;

        return rtrim(rtrim(sprintf('%.12F', $normalized), '0'), '.');
    }

    private static function normalizeJsonValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        if ($normalized === '') {
            return null;
        }

        $decoded = json_decode($normalized, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw ValidationException::withMessages([
                self::FORM_FIELD_MAP['json'] => __('Please enter valid JSON.'),
            ]);
        }

        return json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    private static function summarizeJson(string $value): string
    {
        $decoded = json_decode($value, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return str($value)->limit(90)->toString();
        }

        if (is_array($decoded)) {
            $keys = array_keys($decoded);

            if ($keys === range(0, count($decoded) - 1)) {
                return __('List (:count items)', ['count' => count($decoded)]);
            }

            $summary = collect($keys)
                ->take(3)
                ->implode(', ');

            $remainder = count($keys) > 3 ? __(' and :count more', ['count' => count($keys) - 3]) : '';

            return __('Object: :keys:remainder', [
                'keys' => $summary,
                'remainder' => $remainder,
            ]);
        }

        return str((string) $decoded)->limit(90)->toString();
    }
}
