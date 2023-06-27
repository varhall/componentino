<?php

namespace Varhall\Componentino\UI;


abstract class Modal extends Component
{
    public function open()
    {
        $this->getPresenter()->payload->modals[] = $this->getSnippetId('modal');
        $this->redrawControl('modal');
    }

    public function close()
    {
        $this->getPresenter()->payload->modals[] = $this->getSnippetId('modal');
    }
}