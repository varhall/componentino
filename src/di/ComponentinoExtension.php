<?php

namespace Varhall\Componentino\DI;

use Latte\Engine;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\DI\Definitions\Statement;
use Varhall\Componentino\Latte\ComponentinoMacros;
use Varhall\Componentino\Latte\UIExtension;


class ComponentinoExtension extends \Nette\DI\CompilerExtension
{
    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('componentino'))
            ->setFactory('Varhall\Componentino\Services\ComponentFactory');


        // register Latte extension
        try {
            $latteFactory = $builder->getDefinitionByType(LatteFactory::class);
            \assert($latteFactory instanceof FactoryDefinition);

            $definition = $latteFactory->getResultDefinition();
            \assert($definition instanceof ServiceDefinition);

            // @phpstan-ignore-next-line latte 2 compatibility
            if (\version_compare(Engine::VERSION, '3', '<')) {
                $definition->addSetup('?->onCompile[] = function ($engine) { ' . ComponentinoMacros::class . '::install($engine->getCompiler()); }', ['@self']);
            } else {
                $definition->addSetup('addExtension', [new Statement(UIExtension::class)]);
            }
        } catch (MissingServiceException $e) {
            // ignore
        }
    }
}
