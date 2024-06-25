<?php
namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use App\Repository\DescRepository;

class OpenApiFactory implements OpenApiFactoryInterface
{
    private $decorated;
    private $des;

    public function __construct(OpenApiFactoryInterface $decorated, DescRepository $des)
    {
        $this->decorated = $decorated;
        $this->des = $des;
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);
        return $openApi;
        #$pathItem = $openApi->getPaths();//->getPath('/desc/{id}');
        $pathItem = $openApi->getPaths()->getPath('/tables/{id}');


        #PathItem $test;

        $testOpe = new Operation();
        
        $test = new PathItem(get : $testOpe);
        $desR = $this->des->findAll();
        $param = new Parameter("asd", "path");

        dump($param);
        dump($desR);

        dump($pathItem);
        #dd($test);
        #dd($openApi);

        //return $openApi;
        $operation = $pathItem->getGet();
        #dd($operation);

        
        $openApi->getPaths()->addPath('/test/', $test);
            
            /*, $pathItem->withGet(
            $operation->withParameters(array_merge(
                $operation->getParameters(),
                [new Model\Parameter('fields', 'query', 'Fields to remove of the output')]
            ))
            ));*/

        $openApi = $openApi->withInfo((new Model\Info('New Title', 'v2', 'Description of my custom API'))->withExtensionProperty('info-key', 'Info value'));
        $openApi = $openApi->withExtensionProperty('key', 'Custom x-key value');
        $openApi = $openApi->withExtensionProperty('x-value', 'Custom x-value value');

        return $openApi;
    }
}
