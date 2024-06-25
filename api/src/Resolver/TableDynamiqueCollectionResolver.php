<?php

namespace App\Resolver;

use ApiPlatform\GraphQl\Resolver\QueryCollectionResolverInterface;
use App\Entity\TableDynamique;


final class TableDynamiqueCollectionResolver implements QueryCollectionResolverInterface
{
     /**
     * @param iterable<Book> $collection
     *
     * @return iterable<Book>
     */
    public function __invoke(iterable $collection, array $context): iterable
    {
        // Query arguments are in $context['args'].

        dump($collection);
        foreach ($collection as $book) {
            // Do something with the book.
            dump($book);
        }
        dump($collection);
        dump("asd");

        return $collection;
    }
    public function asd()
    {
        dump("asd");
    }
}
