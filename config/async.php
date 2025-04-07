<?php

return [
    'async' => [
        'engine' => getenv('ASYNC_ENGINE'), // Default to fiber, if not specified in env
    ],
];
