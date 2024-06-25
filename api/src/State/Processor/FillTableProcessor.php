<?php

namespace App\State\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Repository\DescRepository;
use App\Repository\TableRepository;
use App\Repository\TableDynamiqueRepository;
use App\Repository\UserRepository;
use App\Entity\Table;
use App\Entity\BaseUploader;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Bundle\SecurityBundle\Security;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use League\Csv\Reader;
use Shuchkin\SimpleXLSX;

class FillTableProcessor implements ProcessorInterface
{
    public function __construct(private ProcessorInterface $decorated, private DescRepository $descR, private UserRepository $userR, private Security $security, private TableRepository $tableR, private TableDynamiqueRepository $dynaR)
    {}


    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])
    {
        if (!($data instanceof BaseUploader))
            return NULL;
/*
        $csv = Reader::createFromPath('media/'.$data->filePath, 'r')
            ->setHeaderOffset(0);

        $header = $csv->getHeader(); //returns the CSV header record
        $records = $csv->getRecords(); //returns all the CSV records as an Iterator object
        dump($header);
        dump($records);
 */
        /*
        if (!($xlsx = SimpleXLSX::parse('media/'.$data->filePath)))
            dd(SimpleXLSX::parseError());
        foreach ($xlsx->rows() as $r) {
             print_r( $r );
        }
         */




        dd($data);
        
        #dd($this->descR->findAll());
        $descc = $data->table->getCategorie();
        #$desc = $this->descR->findAll()[0];
        $desc = $this->descR->findDesc($descc)[0];//use name ?probably?
        #dd($desc);

        #dd($uriVariables);

        //----------------------------verification
        if (sizeof($header) != sizeof($desc->getDescriptionArray()))
            dd("not same size");
        foreach ($header as $key)
            if (!isset($desc->getDescriptionArray()[$key]))
                dd("not same name");
        //--------------------------enregistrment
        foreach($csv as $records)
        {
            #$desc->getDescriptionArray()
            $datatmp = [];
            foreach ($records as $name => $value) //add all propriete
            {
                dump($desc->getDescriptionArray()[$name]);
                switch ($desc->getDescriptionArray()[$name])
                {
                    case "text":
                        if (!is_string($value))
                            dd("not a text");
                        $datatmp[$name] = $value; 
                        break;
                    case "integer":
                        #dd($value);
                        if (!is_numeric($value))
                            dd("not a integer");
                        $datatmp[$name] = intval($value); 
                        break;
                    case "float":
                        #dd($value);
                        if (!is_numeric($value))
                            dd("not a float");
                        $datatmp[$name] = floatval($value); 
                        break;
                    case "date": //verif later
                        $datatmp[$name] = $value; 
                        #dd($value);
                        #if (!is_date($value))
                        #    dd("not a integer");
                        break;
                }

            }
            $result = $this->dynaR->saveInTable($datatmp, $desc, $data->table->getId(), false);
        }

        #dd($data);
        $this->dynaR->flush();

        #$parameter = json_decode($request->getContent(), true);
        #dd($parameter);
        #dd($request);
    }
}
