<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Table;
use App\Entity\Desc;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\OrganismeRepository;
use App\Repository\UserRepository;
use App\Repository\TableRepository;
use App\Repository\DescRepository;
use App\Controller\Partage\Partage;
use App\Controller\Utils;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ShareProcessor implements ProcessorInterface
{
    public function __construct(private ProcessorInterface $decorated, private DescRepository $descR, private Security $security, private TableRepository $tableR, private UserRepository $userR, private ValidatorInterface $validator)
    {}


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if ($data instanceof Table)
            $shareEntity = "Tables";
        else if ($data instanceof Desc)
            $shareEntity = "Descs";
        else
            return NULL;

        Utils::testUUID($uriVariables["id"], $this->validator);
        Utils::testUUID($uriVariables["userId"], $this->validator);
        #dd($uriVariables);
        #dd($data);
        #
        $user = $this->security->getUser();

        if($uriVariables["userId"] == $user->getId())
            throw new HttpException(422, "it is you");
        if (($receiveUser = $this->userR->find($uriVariables["userId"])) == NULL)
            throw new HttpException(422, "userId invalid");


        $getFunction = "get$shareEntity";
        if (!in_array($uriVariables["id"], $user->$getFunction()))
            throw new HttpException(404, "you don't have this $shareEntity");

        $getRFunction = "getRelated$shareEntity";
        if (in_array($uriVariables["id"], $receiveUser->$getRFunction()))
            throw new HttpException(404, "userId already have this $shareEntity");
        
        $addFunction = "add$shareEntity";
        $this->userR->$addFunction($receiveUser, $uriVariables["id"], false);
        throw new HttpException(204, "done");

    }
}
