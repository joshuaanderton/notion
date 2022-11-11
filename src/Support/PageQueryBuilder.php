<?php

namespace Ja\Notion\Support;

use Illuminate\Support\Str;
use Ja\Notion\Database;
use Ja\Notion\Page;

/**
 * @docs https://developers.notion.com/reference/post-database-query
 */
class PageQueryBuilder
{
    private array $and = [];
    
    private array $or = [];

    private array $sorts = [];

    protected Database $database;

    protected bool $cache = true;

    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function cache(bool $value = true): self
    {
        $this->cache = $value;
        
        return $this;
    }

    private function buildFilter(string $property, string $operator, string|bool|null $value)
    {
        $filter = compact('property');

        if (Str::contains($operator, '.')) {

            [$type, $operator] = explode('.', $operator);

            $filter[$type] = [
                $operator => $value
            ];

        } else {

            $filter[$operator] = $value;

        }

        return $filter;
    }

    public function where(string|array $property, string $operator = null, string|bool $value = null): self
    {
        if (is_array($property)) {
            foreach ($property as $args) {
                $this->and[] = $this->buildFilter(...$args);
            }
        } else {
            $this->and[] = $this->buildFilter($property, $operator, $value);
        }

        return $this;
    }

    public function orWhere(string|array $property, string $operator = null, string|bool $value = null): self
    {
        if (is_array($property)) {
            foreach ($property as $args) {
                $this->or[] = $this->buildFilter(...$args);
            }
        } else {
            $this->or[] = $this->buildFilter($property, $operator, $value);
        }

        return $this;
    }

    public function sortBy(string $propery, string $direction): self
    {
        $this->sorts[] = compact('property', 'direction');

        return $this;
    }

    private function buildQuery(): array
    {
        $query      = [];
        $hasAnd     = count($this->and);
        $hasOr      = count($this->or);
        $hasFilters = $hasAnd || $hasOr;
        $hasSorts   = count($this->sorts);

        if ($hasFilters) {
            $query['filters'] = [];

            if ($hasAnd) {
                $query['filters']['and'] = $this->and;
            }

            if ($hasOr) {
                $query['filters']['or'] = $this->or;
            }
        }

        if ($hasSorts) {
            $query['sorts'] = $this->sorts;
        }

        return $query;
    }

    public function first(): Page|null
    {
        $database = $this->database;

        $results = $database->api->get(
            Page::endpoint(['databaseId' => $database->id]),
            $this->buildQuery()
        );

        if ($result = $results[0] ?? false) {
            return new Page($result, $database);
        }

        return null;
    }

    public function get(): Collection
    {
        $results = $this->database->api->get(
            Page::endpoint(['databaseId' => $this->database->id]),
            $this->query
        );

        $results = new Collection($results ?: []);

        return $results->map(fn ($result) => new Page($result));
    }

}