<?php

namespace Sigawa\Sigawax\Core\CLI\Commands;

use Sigawa\Sigawax\Core\Alias\AliasManager;

class AliasCommandRunner
{
    protected AliasManager $aliasManager;

    public function __construct()
    {
        $this->aliasManager = new AliasManager();
    }

    public function run()
    {
        $command = $this->getCommandFromInput();
        
        switch ($command) {
            case 'alias:list':
                $this->listAliases();
                break;

            case 'alias:resolve':
                $this->resolveAlias();
                break;

            case 'alias:explain':
                $this->explainAlias();
                break;

            case 'alias:suggest':
                $this->suggestAlias();
                break;

            default:
                echo "Unknown command.\n";
                break;
        }
    }

    protected function getCommandFromInput(): string
    {
        // Parse the input, e.g. "sigawax alias:list"
        return $argv[1] ?? '';
    }

    protected function listAliases()
    {
        $aliases = $this->aliasManager->getAliases();
        foreach ($aliases as $alias => $abstract) {
            echo "{$alias} -> {$abstract}\n";
        }
    }

    protected function resolveAlias()
    {
        $alias = $this->getInput('Alias name to resolve: ');
        echo "Resolving alias: {$alias}... \n";

        try {
            $resolved = $this->aliasManager->resolveAlias($alias);
            echo "Resolved: {$resolved}\n";
        } catch (\RuntimeException $e) {
            echo $e->getMessage() . "\n";
        }
    }

    protected function explainAlias()
    {
        $alias = $this->getInput('Alias name to explain: ');
        echo "Explaining alias: {$alias}... \n";

        // Display detailed information about the alias
        // (You can enhance this further with resolution path, etc.)
        $resolved = $this->aliasManager->resolveAlias($alias);
        echo "This alias resolves to: {$resolved}\n";
    }

    protected function suggestAlias()
    {
        $alias = $this->getInput('Unknown alias name: ');
        echo "Suggesting alternatives for: {$alias}\n";

        // Let's simulate some suggestion logic for now (expandable)
        $suggestions = $this->aliasManager->getAliasesForAbstract($alias);
        foreach ($suggestions as $suggestion) {
            echo "Did you mean: {$suggestion}?\n";
        }
    }

    protected function getInput(string $prompt): string
    {
        echo $prompt;
        return trim(fgets(STDIN));
    }
}
