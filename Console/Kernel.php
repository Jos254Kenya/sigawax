<?php 

use Sigawa\Sigawax\Core\CLI\Commands\ExplainServiceCommand; 

$commands = [
    'explain:service' => ExplainServiceCommand::class,
];
$input = $argv[1] ?? null;
$args = array_slice($argv, 2);

// if (isset($commands[$input])) {
//     $command = new $commands[$input](app()->getContainer()); // Assuming `app()` returns your Application
//     $command->handle($args);
// } else {
//     echo "🚀 Available commands:\n";
//     foreach ($commands as $key => $_) {
//         echo "  - php sigawa {$key} <args>\n";
//     }
// }