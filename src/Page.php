<?php

namespace Ja\Notion;

use Carbon\Carbon;
use Illuminate\Support\Str;

use Ja\Notion\Support\Collection;
use Ja\Notion\Exceptions\NotionException;
use Ja\Notion\Objects\NotionObject;
use Ja\Notion\Support\Notion;

class Page extends NotionObject
{
    public static function endpoint(array $params): string
    {
        if (isset($params['id'])) {
            return "pages/{$params['id']}";
        }

        if (isset($params['databaseId'])) {
            return "databases/{$params['databaseId']}/query";
        }

        throw new NotionException(
            "Page endpoint needs [id] (page id) or [databaseId] passed to it"
        );
    }

    protected function build(): void
    {
        $result = $this->attributes;

        $attributes = (
            collect($result)
                ->map(function ($value, $key) {
                    
                    if (is_string($value) && in_array($key, ['created_time', 'last_edited_time'])) {
                        $value = Carbon::parse($value);
                    } else if (is_array($value) && in_array($key, ['icon', 'cover'])) {
                        $value = $value[$value['type']]['url'];
                    } else if (is_array($value) && in_array($key, ['parent'])) {
                        $value = $value[$value['type']];
                    } else if (is_array($value) && in_array($key, ['created_by', 'last_edited_by'])) {
                        $value = $value['id'];
                    }

                    return [$key => $value];
                })
                ->filter(fn ($value, $key) => $key !== 'properties')
                ->collapse()
                ->all()
        );

        $properties = (
            collect($result['properties'])
                ->map(function ($value, $key) {

                    $typeName = $value['type'];
                    $typeValue = $value[$value['type']];

                    if (in_array($typeName, ['title', 'rich_text'])) {

                        $value = $typeValue[0]['plain_text'] ?? '';

                    } else if (in_array($typeName, ['number'])) {

                        $value = $typeValue;

                    } else if ($typeValue !== null) {

                        $value = $typeValue['name'];

                    } else {

                        $value = null;
                    }
                    
                    return [Str::lower($key) => $value];
                })
                ->collapse()
                ->all()
        );
        
        if (Str::contains((string) $attributes['icon'], '.svg')) {
            $attributes['icon'] = file_get_contents(
                (string) $attributes['icon']
            );
        }

        $this->attributes = array_merge(
            $attributes,
            $properties
        );
    }

    protected function getContent(): string|null
    {
        $blocks = $this->blocks();

        if ($blocks->count() === 0) {
            return null;
        }

        $html = '';
        $openTag = null;
        
        foreach ($blocks as $block) {

            if (!$openTag && $block->type === 'bulleted_list_item') {
                $openTag = $block->type;
                $html.= '<ul>';
            }

            if (!$openTag && $block->type === 'numbered_list_item') {
                $openTag = $block->type;
                $html.= '<ol>';
            }

            $html.= $block->toHtml();


            if (!$openTag || $openTag === $block->type) {
                continue;
            }

            if ($openTag === 'bulleted_list_item') {
                $html.= '</ul>';
            }

            if ($openTag === 'numbered_list_item') {
                $html.= '</ol>';
            }

            $openTag = null;
        }

        return $html;
    }

    public function blocks(): Collection
    {
        $blocks = (new Notion)->results(
            endpoint: Block::endpoint(['pageId' => $this->id]),
            data: ['page_size' => 100],
            method: 'get'
        );

        $blocks = new Collection($blocks ?: []);

        return $blocks->map(fn ($result) => new Block($result));
    }
}