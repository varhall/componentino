<?php

namespace Varhall\Componentino\UI;

use Nette\ComponentModel\IComponent;
use Nette\Utils\Random;
use Varhall\Componentino\Services\ComponentFactory;

trait ComponentTrait
{
    /**
     * @var ComponentFactory
     */
    protected $factory = null;

    public function injectFactory(ComponentFactory $factory)
    {
        $this->factory = $factory;
    }

    protected function createComponent(string $name): ?IComponent
    {
        // convert snake_case to CamelCase
        $name = str_replace('_', '', ucwords($name, '_'));
        $name = lcfirst($name);

        // dirty hack - parent method threw an error when null returned
        // return parent::createComponent($name); this is only necessary
        $ucname = ucfirst($name);
        $method = 'createComponent' . $ucname;
        if ($ucname !== $name && method_exists($this, $method) && (new \ReflectionMethod($this, $method))->getName() === $method) {
            return parent::createComponent($name);
        }

        return null;
    }

    public function buildComponent($name, $params, $identifier = null)
    {
        // convert snake_case to CamelCase but keep compatability with macro {control}
        if ($identifier !== false)
            $name = str_replace('_', '', ucwords($name, '_'));

        // create identifier
        if ($identifier === false)
            $identifier = '';

        else if (!$identifier)
            $identifier = Random::generate(6);

        // original createComponent method but recreated
        $ucname = ucfirst($name);
        $method = 'createComponent' . $ucname;

        $fullname = trim("{$name}_{$identifier}", ' _');

        if (isset($this->components[$fullname])) {
            return $this->components[$fullname];
        }

        if (method_exists($this, $method)) {
            $component = call_user_func([ $this, $method ], $params, $identifier);

            if ($component instanceof IComponent) {
                $this->addComponent($component, $fullname);
                return $component;
            }
        }

        $class = get_class($this);
        throw new \Nette\UnexpectedValueException("Method $class::$method() did not return or create the desired component.");
    }

    public function findComponent($path)
    {
        $receivers = explode('-', $path);
        $component = $this;

        foreach ($receivers as $receiver) {
            list($name, $identifier) = array_pad(explode('_', $receiver), 2, false);
            $restoreMethod = "restoreComponent{$name}";

            if (empty($name)) {
                continue;

            } else if (isset($this->components[$name])) {
                $component = $this->components[$name];

            } else if (method_exists($component, $restoreMethod)) {
                $class = new \ReflectionClass(get_class($component));
                $method = $class->getMethod($restoreMethod);
                $method->setAccessible(true);

                $cmp = $method->invokeArgs($component, [ $identifier ]);
                $component->addComponent($cmp, $receiver);
                $component = $cmp;

            } else {
                $component = $component->buildComponent($name, (object) [], $identifier);
            }
        }

        return $component;
    }

    public function openModal($name, $params = null, $identifier = null)
    {
        $modal = $this->buildComponent($name, $params, $identifier);

        if (!($modal instanceof Modal))
            throw new \Nette\InvalidStateException('Given component is not instance of ' . Modal::class);

        $modal->open();
    }

    /**
     * @throws BadSignalException
     */
    public function processSignal(): void
    {
        $signal = $this->readPrivateProperty('signal');

        if ($signal === null) {
            return;
        }

        $component = $this->findComponent($this->readPrivateProperty('signalReceiver'));
        $component->signalReceived($signal);
    }

    private function readPrivateProperty($property)
    {
        $class = new \ReflectionClass(static::class);

        while ($class) {
            try {
                $prop = $class->getProperty($property);
                $prop->setAccessible(true);

                return $prop->getValue($this);

            } catch (\ReflectionException $ex) {
                $class = $class->getParentClass();
            }
        }

        return null;
    }
}