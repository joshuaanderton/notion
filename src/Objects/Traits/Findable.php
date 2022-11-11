<?php

namespace Ja\Notion\Objects\Traits;

use Ja\Notion\Support\Notion;

trait Findable
{
    public static function find(string $id): self|null
    {
        $notion = new Notion;
        $endpoint = static::endpoint(compact('id'));
        $result = $notion->get($endpoint);

        if ($result) {
            return new self($result);
        }

        return null;
    }
}
