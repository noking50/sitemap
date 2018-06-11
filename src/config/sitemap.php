<?php

use Illuminate\Support\Arr;
use Symfony\Component\Finder\Finder;

// option items
$sitemap = [
    'main' => 'official',
];
$sitemap_path = app_path('Http/Sitemap/');

/****************************************************************************************/
// get sitemap file and sort
$config_files = Finder::create()->files()->name('*.php')->in($sitemap_path);
$files = [];
foreach ($config_files as $file) {
    $path = strtolower(str_replace(DIRECTORY_SEPARATOR, '.', $file->getRelativePath()));
    $fname = strtolower(basename($file->getRealPath(), '.php'));
    if ($fname == '_node') {
        $files[$path] = $file->getRealPath();
    } else if(!starts_with($fname, '__')){
        $files[$path . '.' . $fname] = $file->getRealPath();
    }
}
ksort($files);

// build tree array
$sitemap_node = [];
foreach ($files as $k => $v) {
    Arr::set($sitemap_node, $k, array_merge(Arr::get($sitemap_node, $k, []), require $v));
}
$sitemap['node'] = $sitemap_node;

return $sitemap;
