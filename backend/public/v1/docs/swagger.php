<?php
// Builds the OpenAPI (Swagger) document from the @OA comments and returns it as JSON.
// index.html in this folder shows it with Swagger UI.

require_once __DIR__ . '/../../../vendor/autoload.php';

// Comments are not attached to a class or function, so the token analyser is used to find them.
$openapi = \OpenApi\Generator::scan(
    [__DIR__ . '/doc_setup.php', __DIR__ . '/../../../routes', __DIR__ . '/../../../index.php'],
    ['analyser' => new \OpenApi\Analysers\TokenAnalyser()]
);

header('Content-Type: application/json');
echo $openapi->toJson();
