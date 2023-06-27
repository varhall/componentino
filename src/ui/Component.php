<?php

namespace Varhall\Componentino\UI;

use Varhall\Componentino\Services\ComponentFactory;

abstract class Component extends \Nette\Application\UI\Control
{
    use ComponentTrait;

    /**
     * @var ComponentFactory
     */
    protected $factory = null;

    public function getFactory()
    {
        return $this->factory;
    }

    public function setFactory(ComponentFactory $factory)
    {
        $this->factory = $factory;
    }

    public static abstract function selector();

    /**
     * Get component by identifier
     *
     * @param array $input
     * @return Component
     */
    public function setup($input = [])
    {
        foreach ($input as $property => $value) {
            $this->$property = $value;
        }

        return $this;
    }

    protected function templateFile()
    {
        $reflection = new \ReflectionClass($this);
        $class = $reflection->getShortName();
        $directory = dirname($reflection->getFileName());

        return $directory . DIRECTORY_SEPARATOR . "{$class}.latte";
    }

    protected function className()
    {
        $name = str_replace('_', '-', $this->selector());
        return "component--{$name}";
    }

    public function render($args = [])
    {
        /*foreach ($args as $name => $value) {
            if (isset($this->$name))
                $this->$name = $value;
        }*/

        $this->draw((object) $args);

        $classes = [
            $this->className(),
            $args['class'] ?? ''
        ];

        $this->template->class = trim(implode(' ', $classes));
        $this->template->render($this->templateFile());
    }

    protected function draw($args)
    {

    }
}