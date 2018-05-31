<?php

passthru(sprintf(
    'php "%s/../bin/console" cache:clear --env=%s --no-warmup',
    __DIR__,
    $_ENV['APP_ENV']
));

passthru(sprintf(
    'php "%s/../bin/console" doctrine:schema:update -f',
    __DIR__
), $output);

passthru(sprintf(
    'php "%s/../bin/console" doctrine:fixtures:load --env=test --ansi -vvvv --no-interaction',
    __DIR__
));

require __DIR__.'/../vendor/autoload.php';