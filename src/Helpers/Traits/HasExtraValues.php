<?php

namespace Visanduma\LaravelInvoice\Helpers\Traits;

use Visanduma\LaravelInvoice\Models\InvoiceExtra;

trait HasExtraValues {

    public function extra()
    {
        return $this->morphMany(InvoiceExtra::class, 'model');
    }

    public function setExtraValue($key, $value)
    {
        if (is_array($value)) {
            $value = json_encode($value);
        }

        $this->extra()->create([
            'key' => $key,
            'value' => $value
        ]);
    }

    public function setExtraValues(array $values, string $prefix = "")
    {
        foreach ($values as $key => $value) {
            $this->setExtraValue($key, $value);
        }
    }

    public function getExtraValue($key, $default = "")
    {
        $rows = $this->extra()->where('key', $key)->get();

        return $rows->count() > 1
            ? $rows->pluck('value')->toArray()
            : $rows->first()->value ?? $default;
    }

    public function getExtraAttributes($key, $default = "")
    {
        $rows = $this->extra()->where('key', 'LIKE', $key . '%')->get();

        return $rows->count() > 1
            ? $rows->pluck('value')->toArray()
            : $rows->first()->value ?? $default;
    }

    public function getExtraValues($key, bool $preserveKey = true)
    {
        return $this->extra()->where('key', 'like', $key . '.%')->get()->mapWithKeys(function ($itm) use ($key, $preserveKey) {
            $key = $preserveKey ? $itm->key : str($itm->key)->remove($key . '.')->toString();
            return [$key => $itm->value];
        })->toArray();
    }
}
