<?php

namespace Ja\Notion\Support;

use Illuminate\View\Component;

class Blade extends Component
{
    public static function make(string $html, array $props) {
        return view(
            view: static::createBladeViewFromString(app('view'), $html),
            data: $props
        )->render();
    }

    public function render() {}
}