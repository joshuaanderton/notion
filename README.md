## Nifty Notion API helper 

**Only supports for reading/querying right now**

### Install via composer
`composer require joshuaanderton/notion`

### Add API key to environment vars
```
NOTION_API_TOKEN=
NOTION_DATABASE_ID=       # Optional. You can set this later.
```

### Query a database
```
use Ja\Notion\Database;

$databaseId = 'ksil2-...';

$pages = (new Database($databaseId))
            ->pages()
            ->where('title', 'Welcome')
            ->orWhere('slug', 'select')
            ->get();
```

### Use it as a database (it's even caching the response OOB!)
```
$page = (new Database($databaseId))
            ->pages()
            ->where('slug', $request->slug)
            ->first();

dd($page); // Page props without it's content blocks
```

### Page blocks convert `->toHtml()`
```
$pageContent = $page
                  ->blocks()
                  ->map(fn ($block) => $block->toHtml())
                  ->join('');

// A little Tailwind action, and you've got yourself a webpage!
return <<<'blade'

    <div class="prose md:prose-xl">
        {{ $pageContent }}
    </div>

blade;
```