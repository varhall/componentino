<?php

namespace Varhall\Componentino\Latte;

use Latte\Extension;

class UIExtension extends Extension
{
    public function getTags(): array
    {
        return [
            'component'     => [ ComponentNode::class, 'create' ],
            //'errorClass'    => [ static::class, 'macroErrorClass' ]
        ];
    }
}