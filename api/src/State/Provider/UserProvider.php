<?php

namespace App\State\Provider;

use App\Entity;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Controller\Utils;

class UserProvider implements ProviderInterface
{

    public function __construct(private ProviderInterface $decorated_collection, private ProviderInterface $decorated_item, private UserRepository $userR, private Security $security, private ValidatorInterface $validator)
    {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        #dump("provide information");
#        dd($uriVariables);
        #dump($operation);
        #dump($context);
        #dd(($operation->getUriTemplate()));
        if(isset($uriVariables["id"])) Utils::testUUID($uriVariables["id"], $this->validator);
        $user = $this->security->getUser();
        if ($operation instanceof CollectionOperationInterface)
        {
            if ($operation->getUriTemplate() == "/users/tables/{id}")
                return $this->userR->getRelated("Tables", $uriVariables["id"]);
            if ($operation->getUriTemplate() == "/users/descs/{id}")
                return $this->userR->getRelated("Descs", $uriVariables["id"]);
            return $this->userR->findBy(["organisme" =>
                $user->getOrganisme(),
            ]);
        }
        if (!isset($uriVariables["id"]) or $uriVariables["id"] == $user->getId())//get from yourself
            return $user;

        if (!$result = $this->userR->find($uriVariables))
            return NULL;
        if ($result->getOrganisme() != $user->getOrganisme())
            return NULL;
        dump($result);
        // Retrieve the state from somewhere
        return $result;
    }
}
