<?php

namespace Varhall\Componentino\Latte;

use Latte\Compiler\Nodes\Php\Expression\VariableNode;
use Latte\Compiler\Nodes\Php\ExpressionNode;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Tag;

/**
 * {inputError ...}
 */
class ErrorClassNode extends StatementNode
{
    public ExpressionNode $name;


    public static function create(Tag $tag): static
    {
        $tag->outputMode = $tag::OutputKeepIndentation;
        $node = new static;
        if ($tag->parser->isEnd()) {
            trigger_error("Missing argument in {inputError} (on line {$tag->position->line})", E_USER_DEPRECATED);
            $node->name = new VariableNode('ʟ_input');
        } else {
            $node->name = $tag->parser->parseUnquotedStringOrExpression();
        }
        return $node;
    }


    public function print(PrintContext $context): string
    {
        return $context->format(
            'echo Nette\Bridges\FormsLatte\Runtime::item(%node, $this->global)->hasErrors() ? "is-invalid" : "";',
            $this->name,
            $this->position,
        );
    }


    public function &getIterator(): \Generator
    {
        yield $this->name;
    }
}
