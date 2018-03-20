<?php
namespace stubs\AdoreMe\Common\Traits;

use Illuminate\Database\Connection;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Database\Query\Processors\Processor;
use Illuminate\Database\ConnectionResolverInterface as Resolver;

trait PhpSpecMockEloquentTrait
{
    /**
     * Mock eloquent.
     *
     * Do note: The function let cannot be used because of issue https://github.com/phpspec/phpspec/issues/966
     * Instead, copy paste this function in your spec class.
     *
     * @param Resolver|\PhpSpec\Wrapper\Collaborator $resolver
     * @param Connection|\PhpSpec\Wrapper\Collaborator $connectionInterface
     * @param Processor|\PhpSpec\Wrapper\Collaborator $processor
     */
    public function let(Resolver $resolver, Connection $connectionInterface, Processor $processor)
    {
        $this->initLet($resolver, $connectionInterface, $processor);
    }

    /**
     * Make and mock eloquent collaborators.
     *
     * @param $resolver
     * @param $connectionInterface
     * @param $processor
     */
    protected function mockEloquentCollaborators($resolver, $connectionInterface, $processor)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $resolver->connection(null)->willReturn($connectionInterface);
        /** @noinspection PhpUndefinedMethodInspection */
        $connectionInterface->getQueryGrammar()->willReturn(
            new class() extends Grammar
            {
            }
        );
        /** @noinspection PhpUndefinedMethodInspection */
        $connectionInterface->getPostProcessor()->willReturn($processor);
    }

    /**
     * Init the let function.
     *
     * @param $resolver
     * @param $connectionInterface
     * @param $processor
     */
    protected function initLet($resolver, $connectionInterface, $processor)
    {
        $this->mockEloquentCollaborators($resolver, $connectionInterface, $processor);
        /** @noinspection PhpUndefinedMethodInspection */
        $this->setConnectionResolver($resolver);
    }
}
