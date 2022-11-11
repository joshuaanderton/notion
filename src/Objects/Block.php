<?php

namespace Ja\Notion;

use Ja\Notion\Support\Notion;
use Ja\Notion\Support\Collection;
use Ja\Notion\Exceptions\NotionException;
use Ja\Notion\Objects\Traits\Findable;

class Block
{
    use Findable;

    public static function endpoint(array $params): string
    {
        if (isset($params['id'])) {
            return "blocks/{$params['id']}";
        }

        if (isset($params['pageId'])) {
            return "blocks/{$params['pageId']}/children";
        }

        throw new NotionException(
            "Block endpoint needs [id] (block id) or [pageId] passed to it"
        );
    }

    public function toHtml()
    {
        $children = $this->result[$this->type]['rich_text'];

        $content = collect($children)->map(function ($raw) {

            $data = $raw[$raw['type']];
            $link = $data['link']['url'] ?? null;
            $text = $data['content'];
            $text = $this->beautify($raw['annotations'], $text);

            if ($link) {
                return (
                    "<a href=\"{$link}\" target=\"_blank\">{$text}</a>"
                );
            }
                
            return $text;

        })->join('');

        $copyBtn = '<button data-clipboard-target="#clip-' . $this->id . '" class="notion-copy-btn" style="position: absolute; top: 0; right: 0; padding: .6666667em" aria-label="Copy to Clipboard" title="Copy to Clipboard"><svg style="fill: currentColor; height: 17px; width: 17px"xmlns="http://www.w3.org/2000/svg"viewBox="0 0 20 20"fill="currentColor"><path d="M8 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"></path><path d="M6 3a2 2 0 00-2 2v11a2 2 0 002 2h8a2 2 0 002-2V5a2 2 0 00-2-2 3 3 0 01-3 3H9a3 3 0 01-3-3z"></path></svg></button>';

        return match ($this->type) {
            'heading_1'          => "<h2>{$content}</h2>",
            'heading_2'          => "<h3>{$content}</h3>",
            'heading_3'          => "<h4>{$content}</h4>",
            'bulleted_list_item' => "<li>{$content}</li>",
            'numbered_list_item' => "<li>{$content}</li>",
            'paragraph'          => "<p>{$content}</p>",
            'code'               => "<div style=\"position:relative\">{$copyBtn}<pre><code id=\"clip-{$this->id}\">{$content}</code></pre></div>",
            default              => "<p>{$content}</p>",
        };
    }

    private function beautify(array $annotations, string $content): string
    {
        $annotations = (
            collect($annotations)
                ->filter(fn ($apply, $style) => $apply !== false && $style !== 'color')
                ->all()
        );

        foreach ($annotations as $style => $apply) {
            $content = match ($style) {
                'bold'          => "<b>{$content}</b>",
                'italic'        => "<i>{$content}</i>",
                'underline'     => "<u>{$content}</u>",
                'strikethrough' => "<strike>{$content}</strike>",
                'code'          => "<code>{$content}</code>",
            };
        }

        if (!in_array(($color = $annotations['color'] ?? null), [null, 'default'])) {
            $className = Str::endsWith($color, '_background')
                ? 'bg-' . explode('_background', (string) $color)[0] . '-400'
                : "text-{$color}-400";

            $content = "<span class=\"{$className}\">{$content}</span>";
        }
        
        return $content;
    }
}