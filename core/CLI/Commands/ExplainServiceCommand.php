<?php

namespace Sigawa\Sigawax\Core\CLI\Commands;
use Sigawa\Sigawax\Core\Contracts\ContainerInterface;

class ExplainServiceCommand
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function handle(array $args): void
    {
        if (empty($args[0])) {
            echo "🔍 Usage: php sigawa explain:service <abstract>\n";
            return;
        }

        $abstract = $args[0];
        $info = $this->container->explain($abstract);

        if (empty($info)) {
            echo "❌ Service [{$abstract}] is not bound.\n";
            return;
        }

        echo "🔧 Service Explanation for: {$abstract}\n";
        echo str_repeat('-', 50) . "\n";

        foreach ($info as $key => $value) {
            $formatted = is_array($value)
                ? json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
                : (is_bool($value) ? ($value ? '✅ yes' : '❌ no') : $value);

            printf("• %-20s : %s\n", ucfirst(str_replace('_', ' ', $key)), $formatted);
        }

        echo str_repeat('-', 50) . "\n";
    }
}