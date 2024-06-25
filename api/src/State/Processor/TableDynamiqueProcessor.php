<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\TableRepository;
use App\Repository\TableDynamiqueRepository;
use App\Repository\DescRepository;

class TableDynamiqueProcessor implements ProcessorInterface
{
	public function __construct(private ProcessorInterface $decorated_delete, private TableDynamiqueRepository $dynaR, private DescRepository $descR, private TableRepository $tableR)
	{}

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        dump("processor");
        dump($data);
        dump($uriVariables);


        #$desc = ($context["previous_data"]["desc"]);
        $table = $this->tableR->find($uriVariables["id"]);
        $desc = $this->descR->findby(array("categorie" => $table->getCategorie(), "name" => $uriVariables["name"]));

       
        $result = NULL;
        if ($operation->getMethod() == "PUT")
            $result = $this->dynaR->changeInTable($data->data, $context["previous_data"], $desc[0], $uriVariables, true);
        else if ($operation->getMethod() == "POST")
            $result = $this->dynaR->saveInTable($data->data, $desc[0], $uriVariables["id"], true);
        else if ($operation->getMethod() == "DELETE")
        {
            foreach ($data as $dyna_sup)
                $this->decorated_delete->process($dyna_sup, $operation, $uriVariables, $context);
        }
        return $data;
    }
}
