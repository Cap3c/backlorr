<?php

namespace App\State\Provider;

use App\Entity;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\Metadata\CollectionOperationInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Controller\Utils;

use App\Repository\BaseUploaderRepository;
use App\Entity\Table;
use App\Entity\BaseUploader;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use League\Csv\Reader;
use Shuchkin\SimpleXLSX;

class BaseUploaderProvider implements ProviderInterface
{
    public function __construct(private ProviderInterface $decorated_collection, private ProviderInterface $decorated_item, private BaseUploaderRepository $uploadR, private Security $security, private ValidatorInterface $validator)
    {}


    #public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])

    private function is_empty(string $value)
    {
        return ($value == '0' || $value == '');
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        #if (!($data instanceof BaseUploader))
        #    return NULL;
        #$a = $this->decorated_item->findAll();

        $user = $this->security->getUser();
        $collection = $this->uploadR->findby(["proprietaire" => $user]);
        return ($collection);
        dd($a);

        dd($operation);

    }
}
