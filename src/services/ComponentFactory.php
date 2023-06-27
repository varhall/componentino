<?php

namespace Varhall\Componentino\Services;

use Nette\DI\Container;
use Nette\InvalidArgumentException;
use Varhall\Componentino\UI\Component;

class ComponentFactory
{
    protected $components = [];

    protected $container = null;


    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function registerComponent($component)
    {
        $this->checkType($component);
        $selector = $component::selector();

        if (isset($this->components[$selector]))
            throw new InvalidArgumentException("Selector '{$selector}' is already defined");

        $this->components[$selector] = $component;
    }

    /**
     * Creates new component
     *
     * @param $selector
     * @return Component
     * @throws \ReflectionException
     */
    public function create($selector)
    {
        if (!class_exists($selector) && !isset($this->components[$selector]))
            throw new InvalidArgumentException("Component '{$selector}' is not registered");

        $class = new \ReflectionClass(class_exists($selector) ? $selector : $this->components[$selector]);
        $this->checkType($class->name);

        $arguments = [];

        $parameters = $class->getConstructor() ? $class->getConstructor()->getParameters() : [];
        foreach ($parameters as $parameter) {
            $arguments[] = $parameter->getType()->getName() ? $this->getService($parameter->getType()->getName()) : null;
        }

        $component = $class->newInstanceArgs($arguments);
        $component->injectFactory($this);

        return $component;
    }

    protected function checkType($component)
    {
        if (!is_subclass_of($component, Component::class))
            throw new InvalidArgumentException("{$component} is not subclass of " . Component::class);
    }

    protected function getService($type)
    {
        return $this->container->getByType($type);
    }
}