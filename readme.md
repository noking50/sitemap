# Sitemap

Manage Sitemap with  Laravel Route

## Installing

### install
```
composer required noking50/sitemap
```

### config
```
$sitemap = [
    // main site sitemap path
    'main' => 'official',
];

// sitemap files root dir
$sitemap_path = app_path('Http/Sitemap/');
```

## Usage

get current sitemap node
```
Sitemap::node()
```

get some url sitemap node
```
Sitemap::node($path)
```
