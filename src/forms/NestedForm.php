<?php

namespace Varhall\Componentino\Forms;

use Nette\Forms\Control;
use Varhall\Componentino\UI\Component;

abstract class NestedForm extends Component implements Control
{
    /** @var \Nette\Forms\Container */
    public $form = null;

    public function __construct()
    {
        $this->form = new \Nette\Forms\Container();
        $this->addComponent($this->form, 'form');

        $this->initializeControls();
    }

    protected abstract function initializeControls();



    /// IControl

    /**
     * Sets control's value.
     * @param mixed $value
     * @return static
     */
    public function setValue($value)
    {
        $this->form->setValues($value);
    }

    /**
     * Returns control's value.
     * @return mixed
     */
    public function getValue()
    {
        return $this->form->getUntrustedValues();
    }

    public function validate(): void
    {
        $this->form->validate();
    }

    /**
     * Returns errors corresponding to control.
     */
    public function getErrors(): array
    {
        return $this->form->getErrors();
    }

    /**
     * Is control value excluded from $form->getValues() result?
     */
    public function isOmitted(): bool
    {
        return false;
    }

    public function setOption($a, $b)
    {
        // do nothing
    }

    public function getOption($a)
    {
        return null;
    }

    public function isDisabled()
    {
        return false;
    }


    /// Stolen from \Nette\Forms\Controls\BaseControl
    ///   multiple inheritance is not allowed in PHP

    /**
     * Loads HTTP data.
     */
    public function loadHttpData(): void
    {
        $data = $this->getForm()->getHttpData(null, null);

        if (!isset($data[$this->getName()]))
            return;

        $this->setValue($data[$this->getName()]['form']);
    }

    /**
     * Returns form.
     */
    public function getForm(bool $throw = true): ?Form
    {
        return $this->lookup(\Nette\Forms\Form::class, $throw);
    }

    /**
     * Loads HTTP data.
     * @return mixed
     */
    protected function getHttpData($type, string $htmlTail = null)
    {
        return $this->getForm()->getHttpData($type, $this->getHtmlName() . $htmlTail);
    }

    /**
     * Returns HTML name of control.
     */
    public function getHtmlName(): string
    {
        return $this->control->name ?? \Nette\Forms\Helpers::generateHtmlName($this->lookupPath(\Nette\Forms\Form::class));
    }


}