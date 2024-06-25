<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\DescRepository;
use App\Repository\UserRepository;
use App\Entity\Desc;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;

class DescProcessor implements ProcessorInterface
{
    public function __construct(private ProcessorInterface $decorated, private DescRepository $descR, private UserRepository $userR, private Security $security)
    {}

    private function PutOpe($data, $operation, $uriVariables)
    {
        //change meta
        $desc = $this->descR->findby(["categorie" => $uriVariables["categorie"], 'name' => $uriVariables["name"]])[0];

        //description array
        $descriptionArray = $data->getDescriptionArray();
        if ($descriptionArray)
        {
            if ($desc->first_use)
                throw new HttpException(422, "already use, cannot change the data representation");
            $desc->setDescriptionArray($descriptionArray);
        }

        //name
        $name = $data->getName();
        if ($name)
        {
            #dd($this->descR->findDoublon($uriVariables["categorie"], $name));
            if ($this->descR->findDoublon($uriVariables["categorie"], $name))
                throw new HttpException(422, "this name already exist in this categorie");
            $desc->setName($name);
        }
        //description
        $description = $data->getDescription();
        if ($description)
            $desc->setDescription($description);
        $this->descR->updateGlobalBoolean($uriVariables["categorie"], $data->getPartagePublic());
        
        ##dd($desc);
        #$this->decorated->process($desc, $operation, $uriVariables, $context);
        return $desc;
    }


    private function PostOpe($data, $operation, $uriVariables)
    {
        /*if (!$data->getDescriptionArray() or $data->getDescriptionArray() == [NULL])
            throw new HttpException(422, "description empty");
         */
        $this->descR->isValid($data->getDescriptionArray());

        if ($operation->getUriTemplate() == "/descs{._format}")
        {
            $data->setPerm(1);
            #$user = $this->userR->getAdmin($this->security->getUser());
            $user = $this->security->getUser();
            $this->userR->addDescs($user, $data->getCategorie(), true);
        }
        else
        {
            if ($this->descR->findDoublon($uriVariables["categorie"], $data->getName()))
                throw new HttpException(422, "this name already exist in this categorie");
            $permNumber = $this->descR->getNumberOfPerm($uriVariables["categorie"]);
            #dump($permNumber);
            #dump($permNumber[1] + 1);
            $data->setPerm($permNumber[1] + 1);
            #dd($data);
            $data->setCategorie($uriVariables["categorie"]);
            #$data->setName($uriVariables["name"]);
        }
        #return $this->descR->save($data, true);
        return $data;
    }


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        if (!($data instanceof Desc))
            return NULL;
        ##$this->security->denyAccessUnlessGranted('ROLE_CREATE_BASE');

        #dump($context);
        $result = NULL;
        if ($operation instanceof Put)
        {
            dump("asd");
            //change IsPublic
            /*
            if ($operation->getUriTemplate() == "/descs/{categorie}")
            {
                $this->descR->updateGlobalBoolean($uriVariables["categorie"], $data->getPartagePublic());
                return ;
            }
            else
             */
            $result = $this->PutOpe($data, $operation, $uriVariables);
            dump("asd");
        }

        else if ($operation instanceof Post)
            $result = $this->PostOpe($data, $operation, $uriVariables);
            dump("asd");
            #return $this->PostOpe($data, $operation, $uriVariables);
        #return $result;

        dump($data->getCategorie());

        #dd($result);
        // Handle the state

        #$operation =  NEW Post;
        #dd($operation);
        return $this->descR->save($result, true);
        #$this->decorated->process($result, $operation, [], []);
        return NULL;
        return $this->descR->save($result, true);
    }
}
