<?php

namespace Ja\Notion\Objects;

use Exception;
use Illuminate\Support\Str;
use Ja\Notion\Objects\Traits\Findable;

abstract class NotionObject
{
    use Findable;

    public ?array $attributes = null;

    public function __construct(array $attributes = null)
    {
        $this->attributes = $attributes;

        if (method_exists(get_called_class(), 'build')) {
            $this->build();
        }
    }

    abstract public static function endpoint(array $params): string;

    public function __get($name)
    {
        try {
            if (isset($this->$name)) {
                return $this->$name;
            }
        } catch (Exception $e) {
            //
        }

        if (
            !isset($this->attributes[$name]) &&
            method_exists(get_called_class(), $getMethod = 'get' . Str::ucfirst($name))
        ) {
            $this->attributes[$name] = $this->$getMethod();
        }

        return $this->attributes[$name] ?? null;
    }
}