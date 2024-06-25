<?php

namespace App\State\Provider;

use App\Entity;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use App\Repository\DescRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use App\Controller\Utils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DescProvider implements ProviderInterface
{

    public function __construct(private ProviderInterface $decorated_collection, private ProviderInterface $decorated_item, private DescRepository $descR, private Security $security, private ValidatorInterface $validator, private UserRepository $userR)
    {}

    private function Collection(Operation $operation)
    {
        #dd($operation->getUriTemplate());
        if ($operation->getUriTemplate() == "/descs/prive")
        {
            $descs = $this->descR->findDesc($this->security->getUser()->getDescs());
            foreach ($descs as $desc)
                $desc->isShared = (!!$this->userR->getRelated("Tables", $desc->getCategorie()));
            return $descs;
        }
        if ($operation->getUriTemplate() == "/descs/partage")
            return $this->descR->findDesc($this->security->getUser()->getRelatedDescs());
        if ($operation->getUriTemplate() == "/descs/public")
            return $this->descR->findby(["partagePublic" => true]);

        dd("erreur logique");
    }


    private function Item(array $uriVariables)
    {
        if(!$result = $this->descR->findDesc($uriVariables["categorie"], $uriVariables["name"] ?? null))
            return NULL;
        
        $isPublic = ((is_array($result))?$result[0]:$result)->getPartagePublic();
        $user = $this->security->getUser();
        if (in_array($uriVariables["categorie"], $user->getDescs()) or
            in_array($uriVariables["categorie"], $user->getRelatedDescs()) or
            $isPublic)
            return $result;
        return NULL;
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        dump("provide information");
        dump($uriVariables);
        dump($operation);
        dump($context);

        if(isset($uriVariables["categorie"])) Utils::testUUID($uriVariables["categorie"], $this->validator);

        if ($operation instanceof CollectionOperationInterface)
            return $this->Collection($operation);
        if ($operation instanceof Post)
            return ['asd' => 'asd'];//error with cache??
        if ($operation instanceof Put)
        {
            if ($operation->getUriTemplate() == '/descs/{categorie}')
                return ['asd' => 'asd'];//didn't needed
            if (in_array($uriVariables["categorie"], $this->security->getUser()->getDescs()))
                return $this->Item($uriVariables);//i dont known
            return NULL;
        }
        return $this->Item($uriVariables);
    }
}
