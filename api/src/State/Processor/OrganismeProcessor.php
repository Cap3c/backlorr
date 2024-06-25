<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\OrganismeRepository;
use App\Repository\UserRepository;
#use App\State\UserRepository;
use App\Entity\Organisme;
use App\Entity\User;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\SecurityBundle\Security;

class OrganismeProcessor implements ProcessorInterface
{
    public function __construct(
        private ProcessorInterface $decorated,
        private OrganismeRepository $dyna,
        private UserRepository $userR,
        private Security $security)
	{}

	public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {

        if (!($data instanceof Organisme))
            return NULL;

        dump($data);
        $mmembre = $data->getAdmin();
        if (!isset($mmembre["email"]) || !isset($mmembre["username"]) || !$data)
            throw new HttpException(422, "user has a bad input");

        if ($this->userR->findby(["email" => $mmembre["email"]]))
            throw new HttpException(422, "this email already exist");

        $user = new User();
        $user->fillAdmin($mmembre["email"], $this->userR->hash($user, "1234"), $mmembre["username"], $data);
        dump($user);
        #$data->setAdminID($user);//i forgot

        $this->userR->save($user, false);
        $result = $this->dyna->save($data, true);
        #$this->userR->process($user, $operation);
		// Handle the state
        return $result;
	}
}
