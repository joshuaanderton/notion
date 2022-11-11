<?php

namespace Ja\Notion;

use Ja\Notion\Exceptions\NotionException;
use Ja\Notion\Objects\NotionObject;
use Ja\Notion\Support\PageQueryBuilder;

/**
 * @resource https://developers.notion.com/reference
 */
class Database extends NotionObject
{
    public static function endpoint(array $params): string
    {
        if (isset($params['id'])) {
            return "database/{$params['id']}";
        }

        throw new NotionException(
            "Database endpoint needs [id] (database id)"
        );
    }

    public function getId()
    {
        return env('NOTION_DATABASE_ID', null);
    }

    public function pages()
    {
        return new PageQueryBuilder($this);
    }
}