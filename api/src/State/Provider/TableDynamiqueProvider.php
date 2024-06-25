<?php

namespace App\State\Provider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Repository\TableDynamiqueRepository;
use App\Repository\TableRepository;
use App\Entity\TableDynamique;
use App\Repository\DescRepository;
use App\Repository\PermissionRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Controller\Utils;

class TableDynamiqueProvider implements ProviderInterface
{
    public function __construct(private ProviderInterface $decorated_collection, private ProviderInterface $decorated_item, private TableRepository $tableR, private TableDynamiqueRepository $dynaR, private DescRepository $descR, private Security $security, private PermissionRepository $permR, private ValidatorInterface $validator)
    {}

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        dump("provider");

        if(isset($uriVariables["id"])) Utils::testUUID($uriVariables["id"], $this->validator);
        
        $user = $this->security->getUser();
        #$this->security->denyAccessUnlessGranted('ROLE_INTERACT_BASE');
        #if ($this->isGranted("ROLE_orga_admin"))
        #    dd("this should never appair");
            #throw new HttpException(401, "admin can't interact with data");
        if (!$this->security->isGranted("ROLE_cap3c_R&D"))
        {
            if (!$perm = ($this->permR->permissionGranted($uriVariables["id"], $user->getId())))
                throw new HttpException(401, "You don't have this permission.");
            if (sizeof($perm) != 1)
                throw new HttpException(500, "number of permission should be 1");
            $perm = $perm[0];
        }

        if (!$table = $this->tableR->find($uriVariables["id"]))
            throw new HttpException(500, "you cant see this message");
        if (!$desc = $this->descR->findby(array("categorie" => $table->getCategorie(), "name" => $uriVariables["name"])))
            throw new HttpException(400, "The name of the categorie does not refer to this table or has not been found");

        switch ($operation->getMethod())//update to post
        {
            case "POST":
                $permMethode = 0;
                break;
            case "GET":
                $permMethode = 1;
                break;
            case "PUT":
                $permMethode = 2;
                break;
            case "DELETE":
                $permMethode = 3;
                break;
            default:
                throw new HttpException(401, "methode undefined, and you should not get this message");
        }

        if (!$this->security->isGranted("ROLE_cap3c_R&D"))

            #dd(($desc[0]->getPerm()));
            #dd($perm->getValue());
            if (!(hexdec($perm->getValue()[$desc[0]->getPerm() - 1]) & (1<<$permMethode)))
                throw new HttpException(401, "You don't have this permission.");

        if ($permMethode == 1)//GET
        {
            $table->data = $this->dynaR->getCollection($desc[0], $uriVariables["id"], $context, $permMethode);
          return $table;
        }
        if ($permMethode == 0)
            return []; //["desc" => $desc];
        
        if ($permMethode == 3 && $operation->getUriTemplate() == "/tables/{id}/{name}/filter")//DELETE
          return ($this->dynaR->getCollection($desc[0], $uriVariables["id"], $context, $permMethode));
        $result = ($this->dynaR->getById($desc[0], $uriVariables["id"], $uriVariables["idInTable"]));

        return $result;
    }
}
