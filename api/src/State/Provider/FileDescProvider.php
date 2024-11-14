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

use App\Repository\DescRepository;
use App\Repository\TableRepository;
use App\Repository\TableDynamiqueRepository;
use App\Repository\UserRepository;
use App\Repository\FileRepository;
use App\Entity\Table;
use App\Entity\BaseUploader;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Post;
use League\Csv\Reader;
use Shuchkin\SimpleXLSX;

class FileDescProvider implements ProviderInterface
{
    public function __construct(private ProviderInterface $decorated_collection, private ProviderInterface $decorated_item, private UserRepository $userR, private Security $security, private ValidatorInterface $validator, private FileRepository $fileR)
    #public function __construct(private ProcessorInterface $decorated, private DescRepository $descR, private UserRepository $userR, private Security $security, private TableRepository $tableR, private TableDynamiqueRepository $dynaR)
    {}


    #public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = [])

    private function is_empty(string $value)
    {
        return ($value == '0' || $value == '');
    }

    private function estimation($xlsx, $sheetNumber = 0)
    {
        $estimDesc = [];
        $i = 0;
        foreach ($xlsx->rows($sheetNumber, 1)[0] as $rName) {
            #dd($r);
            #dump($r);
            $estimation = "empty";
            #foreach($j=1; $j<5; $j++) {
            $first = true;
            foreach ($xlsx->rows($sheetNumber, 500) as $rData) {
                if ($first)
                {
                    $first = false;
                    continue;
                }
                #dd($rData);
                /*
                dump( $rData[$i] );
                dump(is_float($rData[$i])); //float
                dump(is_int($rData[$i])); //integer
                dump(is_numeric($rData[$i])); //integer !
                dump(is_string($rData[$i])); //string
                dump($this->is_empty($rData[$i])); //empty
                 */

                if ($this->is_empty($rData[$i]))
                {
                    continue;
                }
                if (is_numeric($rData[$i]))
                {
                    if (-2147483648 > (int) $rData[$i] || (int) $rData[$i] > 2147483647)
                    {
                        $estimation = "text";
                        break;
                    }
                    #$estimation = ($estimation == "float") ? $estimation : "integer";
                    $estimation = "integer";
                    continue;
                }
                if (is_string($rData[$i]))
                {
                    $estimation = "text";
                    break;
                }
                #if (is_float($rData[$i]))
                #{
                #    $estimation = "float";
                #}

            }

            $estimDesc[$rName] = $estimation;
             #print_r( $r );
            $i++;
        }
        return ($estimDesc);

    }

    private function provideXLSX($data)
    {
        if (!($xlsx = SimpleXLSX::parseFile('media/'.$data->filePath)))
            dd(SimpleXLSX::parseError());

        $result = [];
        $position_num = strpos($data->filePath, "-");
        $result["name"] = substr($data->filePath, 0, $position_num);
        $sheetInc = 0;
        while ($xlsx->sheetName($sheetInc))
        {
            $info = [];
            $info["name"] = $xlsx->sheetName($sheetInc);
            $info["description"] = 'a remplir';
            $info["descriptionArray"] = $this->estimation($xlsx, $sheetInc);
            $result[] = $info;
            $sheetInc++;
        }

        return [$result];

        dd($uriVariables["id"]);

    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        #$a = $this->decorated_item->findAll();

        $data = $this->fileR->find($uriVariables["id"]);
        dump($data);
        return ($this->provideXLSX($data));

        #if (!($data instanceof BaseUploader))
        #    return NULL;
/*
        $csv = Reader::createFromPath('media/'.$data->filePath, 'r')
            ->setHeaderOffset(0);

        $header = $csv->getHeader(); //returns the CSV header record
        $records = $csv->getRecords(); //returns all the CSV records as an Iterator object
        dump($header);
        dump($records);
 */





        #dd($data);

        #dd($this->descR->findAll());
        #$descc = $data->table->getCategorie();
        #$desc = $this->descR->findAll()[0];
        #$desc = $this->descR->findDesc($descc)[0];//use name ?probably?
        #dd($desc);

        #dd($uriVariables);

        /*
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
         */

        #$parameter = json_decode($request->getContent(), true);
        #dd($parameter);
        #dd($request);
    }
}
