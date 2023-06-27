<?php

namespace Varhall\Componentino\Forms;

use Nette;
use Varhall\Componentino\UI\ComponentTrait;

class Form extends \Nette\Application\UI\Form
{
    use ComponentTrait;

    /** @var IFormStyle */
    public $style = null;

    public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null)
    {
        parent::__construct($parent, $name);

        $this->style = new Bootstrap4FormStyle();
        $this->onRender[] = function() {
            $this->style->setup($this);
        };
    }

    public function render(...$args): void
    {
        parent::render(null);
    }
}