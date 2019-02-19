<?php

use Sami\Sami;
use Sami\RemoteRepository\GitHubRemoteRepository;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in($dir = __DIR__ . '/src');

return new Sami($iterator, [
    'title' => 'GlobalPayments\Api',
    'build_dir' => dirname($dir) . '/docs',
    'cache_dir' => dirname($dir) . '/docs-cache',
    'remote_repository' => new GitHubRemoteRepository('globalpayments/php-sdk', dirname($dir)),
]);
