<?php

namespace Varhall\Componentino\Latte;

use Latte\CompileException;
use Latte\Macros\BlockMacros;
use Nette\Utils\Strings;

class ComponentinoMacros extends \Latte\Macros\MacroSet
{
    public static function install(\Latte\Compiler $compiler)
    {
        $set = new static($compiler);
        //$nette = new BlockMacros($compiler);

        $set->addMacro('component', [ $set, 'macroComponent' ]);
        $set->addMacro('errorClass', [ $set, 'macroErrorClass' ]);
    }

    public function macroComponent(\Latte\MacroNode $node, \Latte\PhpWriter $writer)
    {
        $words = $node->tokenizer->fetchWords();
        if (!$words) {
            throw new CompileException('Missing component name in {component}');
        }
        $name = $writer->formatWord($words[0]);
        $identifier = $words[1] ?? null;
        $method = ucfirst($words[2] ?? '');
        $method = Strings::match($method, '#^\w*$#D') ? "render$method" : "{\"render$method\"}";

        $tokens = $node->tokenizer;
        $pos = $tokens->position;
        $param = $writer->formatArray();
        $tokens->position = $pos;
        while ($tokens->nextToken()) {
            if ($tokens->isCurrent('=>') && !$tokens->depth) {
                $wrap = true;
                break;
            }
        }

        if (empty($identifier)) {
            $identifier = 'null';

        } else if (!preg_match('/^\$/i', $identifier)) {
            $identifier = "'{$identifier}'";
        }

        if (empty($wrap)) {
            $param = substr($param, 1, -1); // removes array() or []
        }

        if (empty($param)) {
            $param = '[]';
        }

        return " /* line $node->startLine */ "
            . ($name[0] === '$' ? "if (is_object($name)) \$_tmp = $name; else " : '')
            . '$_tmp = $this->global->uiControl->buildComponent(' . $name . ', (object) '. $param . ', ' . $identifier . '); '
            . 'if ($_tmp instanceof Nette\Application\UI\IRenderable) $_tmp->redrawControl(null, false); '
            . ($node->modifiers === ''
                ? "\$_tmp->$method($param);"
                : $writer->write("ob_start(function () {}); \$_tmp->$method($param); echo %modify(ob_get_clean());")
            );
    }

    public function macroErrorClass(\Latte\MacroNode $node, \Latte\PhpWriter $writer)
    {
        $words = $node->tokenizer->fetchWords();
        if (!$words) {
            throw new CompileException('Missing element name in {errorClass}');
        }
        $name = $writer->formatWord($words[0]);


        return $writer->write("echo \$form[$name]->hasErrors() ? 'is-invalid' : '';");
    }
}