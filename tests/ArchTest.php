<?php

declare(strict_types=1);

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray'])
    ->each->not->toBeUsed();

arch()->preset()->php();

arch()->preset()->security();

arch('annotations')
    ->expect('Akira\LaravelAuthLogs')
    ->toHaveMethodsDocumented();
