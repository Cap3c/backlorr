<?php

namespace App\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryItemResolverInterface;
use App\Entity\TableDynamique;


final class TableDynamiqueResolver implements QueryItemResolverInterface
{
     /**
     * @param iterable<Book> $collection
     *
     * @return iterable<Book>
     */
    public function __invoke(?object $item, array $context): object
    {
        // Query arguments are in $context['args'].

        dump($item);
        dump($context);

#        return NULL;
        return $item;
    }
    public function asd()
    {
        dump("asd");
    }
}
