<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\LevelSetList;
use Rector\CodingStyle\Rector\Class_\AddConstantModifierRector;
use Rector\DeadCode\Rector\If_\RemoveDeadInstanceOfRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ParamTypeFromStrictTypedPropertyRector;
use Rector\TypeDeclaration\Rector\FunctionLike\ReturnTypeFromStrictTypedPropertyRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/',
    ]);

    $rectorConfig->skip([
        __DIR__ . '/vendor',
        // evita tocar ficheros de build o plantillas muy frágiles
        '**/node_modules/*',
        '**/*.min.php',
    ]);

    // Sube sintaxis y *rules* hasta PHP 8.4
    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_84,
    ]);

    // Reglas seguras y útiles para este caso
    $rectorConfig->rules([
        AddConstantModifierRector::class,
        RemoveDeadInstanceOfRector::class,
        ParamTypeFromStrictTypedPropertyRector::class,
        ReturnTypeFromStrictTypedPropertyRector::class,
    ]);

    // Evita añadir tipos en funciones WP “mágicas” (filtra por patrones si hace falta)
    $rectorConfig->disableParallel(); // CI más determinista en repos grandes
};
