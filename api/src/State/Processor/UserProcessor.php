<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\UserRepository;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\HttpException;
use ApiPlatform\Metadata\Put;

class UserProcessor implements ProcessorInterface
{
	public function __construct(private ProcessorInterface $decorated, private UserRepository $userR, private Security $security)
	{}

    private function updateCreditential(mixed $data, Operation $operation, array $uriVariables, array $context)
    {
        dump($data);
        $user = $this->security->getUser(); //only connected user
        $pass = $data->getPlainPassword();
        $data->eraseCredentials();
        if ($pass)
        {
            $user->newPass = false;
            $user->setPassword($this->userR->hash($user, $pass));
        }
        $email = $data->getEmail();
        if ($email)
        {
            //dd($this->userR->findby(['email' => $email]));
            $user->setEmail($email);
        }
        $name = $data->getName();
        if ($name)
            $user->setName($name);
        $result = $this->decorated->process($user, $operation, $uriVariables, $context);
        return $result;
    }

	public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []) : NULL | User
    {
        if (!($data instanceof User))
            return NULL;

        if ($operation instanceof Put)
            return $this->updateCreditential($data, $operation, $uriVariables, $context);

        ##$this->security->denyAccessUnlessGranted('ROLE_ADMIN');

        //----------------------set password
        $data->setPassword($this->userR->hash($data, "1234"));
        $data->eraseCredentials();

        //------------------------organisme------------------------------

        #if (!in_array("ROLE_orga_admin", $this->security->getUser()->getRoles()))
        #    throw new HttpException(500, "what append??");

        $data->setOrganisme($this->security->getUser()->getOrganisme());

        //------------------------roles------------------------------
        if ($this->security->getUser()->getOrganisme()->getName() != "cap3c")
            $data->setRoles(["ROLE_orga_user"]);
        else //cap3c
        {
            if (in_array("ROLE_cap3c_support_tech", $data->getRoles()))
                $data->setRoles(["ROLE_cap3c_support_tech"]);
            else if (in_array("ROLE_cap3c_R&D", $data->getRoles()))
                $data->setRoles(["ROLE_cap3c_R&D"]);
            else
                throw new HttpException(401, "you need to define role between 'ROLE_cap3c_support_tech' and 'ROLE_cap3c_R&D'");
        }

        //------------------------save------------------------------

        #$result = $this->decorated->process($data, $operation, $uriVariables, $context);
        $result = $this->userR->save($data, true);
		// Handle the state
        return $result;
	}
}
