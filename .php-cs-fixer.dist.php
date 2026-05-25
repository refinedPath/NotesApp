<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src');

$config = new PhpCsFixer\Config();
$config->setRules([
    '@PSR12' => true,
]);
$config->setIndent('  ');
$config->setLineEnding("\n");
$config->setFinder($finder);

return $config;
