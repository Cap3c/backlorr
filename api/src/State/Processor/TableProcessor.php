<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\TableRepository;
use App\Repository\TableDynamiqueRepository;
use App\Repository\DescRepository;
use App\Repository\UserRepository;
use App\Entity\Table;
use App\Entity\Desc;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\HttpException;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;

class TableProcessor implements ProcessorInterface
{
	public function __construct(private ProcessorInterface $decorated, private TableRepository $tableR, private DescRepository $descR, private TableDynamiqueRepository $dynaR, private UserRepository $userR, private Security $security)
	{}

    private function condition($user, $data)
    {
        $desc = $this->descR->findDesc($data->getCategorie())[0];
        if(!(
            (
                $user->getDescs() &&
                in_array($data->getCategorie(), $user->getDescs())
            ) ||
            (
                $user->getRelatedDescs() &&
                in_array($data->getCategorie(), $user->getRelatedDescs())
            ) ||
            $desc->getPartagePublic()
        ))
            throw new HttpException(401, "you don't have access to this description");


        if (($this->tableR->findby(array("categorie" => $data->getCategorie(), "name" => $data->getName()))) != [])
            throw new HttpException(401, "this table name already exist");
    }

    private function PutOpe($data, $uriVariables)
    {
         $table = $this->tableR->find($uriVariables["id"]);
    
         if ($data->getName())
             $table->setName($data->getName());
         $table->setPartagePublic($data->getPartagePublic());
         return $table;
    }

    private function PostOpe($data)
    {
        $user = $this->security->getUser();
        
        $this->condition($user, $data);
        if (($desc = $this->descR->findby(array("categorie" => $data->getCategorie()))) === [])#get all description for new table 
            throw new HttpException(401, "description not found??");

        #dd($desc);

        $this->userR->addTables($user, $data->getId(), true);
        $this->dynaR->save($desc, $data->getId(), true);
        $this->descR->updateGlobalBoolean($data->getCategorie(), true, "first_use");
        return $data;
    }

	public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
	{
        dump($data);
        dump($context);

        if ($operation instanceof Put)
        {
            $result = $this->PutOpe($data, $uriVariables);
        }
        else
        {
            $result = $this->PostOpe($data);
        }
        
        

        #$this->security->denyAccessUnlessGranted('ROLE_CREATE_BASE');
        #$user = $this->userR->getAdmin($this->security->getUser()); //probably support

        #$result = $this->tableR->save($result, true);
        $this->decorated->process($result, $operation, $uriVariables, $context);
		// Handle the state
        return $result;
	}
}
