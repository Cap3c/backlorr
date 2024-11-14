<?php

namespace App\State\Provider;

use App\Entity;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use App\Repository\TableRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Controller\Utils;

class TableProvider implements ProviderInterface
{

    public function __construct(private ProviderInterface $decorated_collection, private ProviderInterface $decorated_item, private TableRepository $tableR, private Security $security, private ValidatorInterface $validator, private UserRepository $userR)
    {}

    private function collectionOpe(Operation $operation)
        {
        #$this->security->denyAccessUnlessGranted('ROLE_CREATE_BASE');//normally useless but access denied in (security parameter in Entity) work after
        if ($operation->getUriTemplate() == "/tables/prive")
        {
            $table = $this->tableR->findby(["id" => $this->security->getUser()->getTables()]);
            foreach ($table as $value)
                $value->isShared = (!!$this->userR->getRelated("Tables", $value->getId()));
            return $table;
        }
        if ($operation->getUriTemplate() == "/tables/partage")
            return $this->tableR->findby(["id" => $this->security->getUser()->getRelatedTables()]);
        if ($operation->getUriTemplate() == "/tables/public")
            return $this->tableR->findby(["partagePublic" => true]);



        #if ($this->security->is_granted("ROLE_INTERACT_BASE"))
        #    dd("asd");
        $result = [];
        foreach ($this->security->getUser()->getPermissions() as $val)
            $result[] = $val->getTables();
        return($result);
        #dd($result);
        return $this->tableR->findby(["id" => $result]);
        #dd($result);
        #return $this->tableR->findby(["id" =>
        #    $this->security->getUser()->getTables() +
        #    $this->security->getUser()->getRelatedTables()
        #]);
        }

    private function ItemOpe(array $uriVariables)
    {
        #if ()
        #$this->security->denyAccessUnlessGranted('ROLE_CREATE_BASE');//normally useless but access denied in (security parameter in Entity) work after
        return $this->tableR->getItem($uriVariables["id"]);
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if(isset($uriVariables["id"])) Utils::testUUID($uriVariables["id"], $this->validator);
        #dump("provide information");
        #dump($uriVariables);
        #dump($operation);
        #dump($context);
        if ($operation instanceof CollectionOperationInterface)
            return $this->collectionOpe($operation);
        if ($operation instanceof Post)
            return ['asd' => 'asd'];
        if ($operation instanceof Put)
            return ['asd' => 'asd'];
        return $this->ItemOpe($uriVariables);
        // Retrieve the state from somewhere
        return $result;
    }
}
