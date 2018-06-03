<?php

passthru(sprintf(
    'php "%s/../bin/console" cache:clear --env=%s --no-warmup',
    __DIR__,
    $_ENV['APP_ENV']
));

if($_ENV['UPDATE_SCHEMA']) {
    passthru(sprintf(
        'php "%s/../bin/console" doctrine:schema:update -f',
        __DIR__
    ), $output);
}


if($_ENV['LOAD_FIXTURES']) {
    passthru(sprintf(
        'php "%s/../bin/console" doctrine:fixtures:load --env=test --ansi -vvvv --no-interaction',
        __DIR__
    ));
}

require __DIR__.'/../vendor/autoload.php';