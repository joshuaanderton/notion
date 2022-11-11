<?php

namespace Ja\Notion\Objects;

use Ja\Notion\Database;
use Ja\Notion\Support\Collection;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Str;
use Ja\Notion\Exceptions\NotionException;
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

        return $this->attributes[$name] ?? null;
    }
}