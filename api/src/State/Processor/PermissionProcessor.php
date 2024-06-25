<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\DescRepository;
use App\Repository\PermissionRepository;
#use App\Entity\Desc;
use App\Entity\Permission;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;

use Aza\Components\Math\BigNumber;
use Aza\Components\Math\NumeralSystem;

class PermissionProcessor implements ProcessorInterface
{
    public function __construct(private ProcessorInterface $decorated, private DescRepository $descR, private PermissionRepository $permissionR, private Security $security)
    {}

    private function verifyValue($data, $table)
    {

        $permValue = $data->getValue();
        $permNumber = $this->descR->getNumberOfPerm($table->getCategorie())[1];
        dump($permValue);
        dump($permNumber);
        if(!preg_match('/^[0-9a-fA-F]+$/', $permValue))
            throw new HttpException(422, "permission value need to be in hexa");
        #if(($permValue >> ($permNumber << 2)))//4 differents permissions
        #    throw new HttpException(422, "permission value are too big");
        if (strlen($permValue) != $permNumber)
            throw new HttpException(422, "this categorie have ".$permNumber." table(s), and you defined ".strlen($permValue)." permission(s)");
        $res = NumeralSystem::convert($permValue, 16, 10);

    }

    private function verifyRole($user, $table)
    {
        $admin = $this->security->getUser();
        dump($admin);
        dump($user);
        dump($user->getRoles()[0]);
        if ($user->getOrganisme() != $admin->getOrganisme())
            throw new HttpException(422, "this user do not exist");
        #$this->security->denyAccessUnlessGranted('ROLE_orga_user');
        if ($user->getRoles()[0] != "ROLE_orga_user")//user is specific user
            throw new HttpException(422, "only user can have a permission");
        if ($this->permissionR->findby(["users" => $user->getId(), "tables" => $table->getId()]))
            throw new HttpException(422, "User already has a permission for this table.");
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        if (!($data instanceof Permission))
            return NULL;
       if ($operation instanceof Put)
       {
           $val = $data->getValue();
            $data = $context["previous_data"];
           $data->setValue($val);
       }
       # return $data;
            #dd($uriVariables);
        $table = $data->getTables();
        if(!($table))
            dd($data);
        if ($operation instanceof Post)
            $this->verifyRole($data->getUsers(), $table);//post only
        $this->verifyValue($data, $table, $operation); //post and put
        $result = $this->decorated->process($data, $operation, $uriVariables, $context);

        // Handle the state
        return $data;
    }
}
