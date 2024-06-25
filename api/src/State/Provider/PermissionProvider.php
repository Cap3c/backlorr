<?php

namespace App\State\Provider;

use App\Entity;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Put;
use App\Repository\PermissionRepository;
use App\Repository\UserRepository;
use App\Repository\DescRepository;
use Symfony\Bundle\SecurityBundle\Security;

class PermissionProvider implements ProviderInterface
{

    public function __construct(private ProviderInterface $decorated_collection, private ProviderInterface $decorated_item, private UserRepository $userR, private PermissionRepository $permR, private Security $security, private DescRepository $descR)
    {}

    private function collectionOpe($user)
    {
        $result = NULL;
            if ($this->security->isGranted("ROLE_orga_user"))
                $result = $this->permR->findby(["users" => $user->getId()]);
            else
                $result = $this->permR->findby(["users" => $this->userR->findby(["organisme" => $user->getOrganisme()])]);
        return $result;
    }

    private function ItemOpe($uriVariables, $user)
    {
        $result = $this->permR->find($uriVariables);
        if (!$result)
            return NULL;
        if ($result->getUsers() != $user and
            !$this->security->isGranted("ROLE_orga_admin"))
            return NULL;
        $cat = $result->getTables()->getCategorie();
        $result->desc = $this->descR->findby(["categorie" => $cat]);
        #dd($result);
        return $result;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        dump("provide information");
        #dump($uriVariables);
        #dump($operation);
        #dump($context);
        $user = $this->security->getUser();
        if ($operation instanceof CollectionOperationInterface)
            return $this->collectionOpe($user);
        if ($operation instanceof ItemOperationInterface)
            return $this->ItemOpe($uriVariables, $user);
        #if ($operation instanceof Put)
        $result = $this->ItemOpe($uriVariables, $user);
        return $result;
        dd("catch");
        return NULL;
    }
}
