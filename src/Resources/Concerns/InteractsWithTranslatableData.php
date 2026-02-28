<?php

namespace NinjaPortal\Admin\Resources\Concerns;

use Illuminate\Database\Eloquent\Model;

trait InteractsWithTranslatableData
{
    protected static function withTranslatableFormData(array $data, ?Model $record = null): array
    {
        if (! $record || ! method_exists($record, 'getTranslatableAttributes')) {
            return $data;
        }

        $record->loadMissing('translations');

        foreach ($record->translations as $translation) {
            $locale = (string) $translation->getAttribute('locale');
            $data[$locale] = array_merge(
                $data[$locale] ?? [],
                array_filter(
                    $translation->only($record->getTranslatableAttributes()),
                    static fn ($value) => $value !== null,
                ),
            );
        }

        return $data;
    }
}
