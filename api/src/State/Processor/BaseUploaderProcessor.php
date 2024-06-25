<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\UserRepository;
use App\Entity\BaseUploader;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\HttpException;
use ApiPlatform\Metadata\Put;

class BaseUploaderProcessor implements ProcessorInterface
{
	public function __construct(private ProcessorInterface $decorated, private UserRepository $userR, private Security $security)
	{}

	public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []) : NULL | User
    {
        if (!($data instanceof BaseUploader))
            return NULL;

        dd($data);
        $descCat = $request->request->get("desc");
        if (empty($descCat))
            throw new BadRequestHttpException('"desc" is required'); //categorie ??
        $desc = $this->descR->findDesc($descCat);
        if (empty($desc))
            throw new BadRequestHttpException('"desc" is wrong'); //categorie ??

        $result = $this->decorated->process($data, $operation, $uriVariables, $context);
        #$result = $this->userR->save($data, true);
		// Handle the state
        return $result;
	}
}
