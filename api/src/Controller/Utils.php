<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints\Uuid as UuidConstraint;
use Symfony\Component\HttpKernel\Exception\HttpException;

class Utils extends AbstractController
{
    public function __construct(private ValidatorInterface $validator)
    {}


    static public function testUUID(string $uuid, ValidatorInterface $validator)
    {
        if ($uuid == NULL)
            return ;
        $uuidContraint = new UuidConstraint();
        $errors = $validator->validate($uuid, $uuidContraint);
       # ValidatorInterface::validate($uuid, $uuidContraint);
        if (count($errors))
            throw new HttpException(422, $errors);
    }
}
