<?php

namespace Varhall\Componentino\DI;

use Nette\DI\Config\Helpers;
use Varhall\Componentino\Services\ComponentFactory;

/**
 * Description of ComponentinoExtension
 *
 * @author fero
 */
class ComponentinoExtension extends \Nette\DI\CompilerExtension
{
    protected function configuration()
    {
        return Helpers::merge($this->getConfig(), [
            'components'    => [],
        ]);
    }

    /**
     * Processes configuration data
     *
     * @return void
     */
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        $builder->addDefinition($this->prefix('componentino'))
            ->setFactory('Varhall\Componentino\Services\ComponentFactory');
    }

    public function beforeCompile()
    {
        parent::beforeCompile();

        $config = $this->configuration();
        $builder = $this->getContainerBuilder();

        foreach ($builder->findByType(ComponentFactory::class) as $definition) {
            foreach ($config['components'] as $component) {
                $definition->addSetup('registerComponent', [ $component ]);
            }
        }
    }


}
