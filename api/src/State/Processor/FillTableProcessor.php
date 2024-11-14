<?php

namespace App\State\Processor;

use Doctrine\Persistence\ManagerRegistry;
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
    public function __construct(private ManagerRegistry $registry, private ProcessorInterface $decorated, private DescRepository $descR, private UserRepository $userR, private Security $security, private TableRepository $tableR, private TableDynamiqueRepository $dynaR)
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
        if (!($xlsx = SimpleXLSX::parse('media/'.$data->filePath)))
            dd(SimpleXLSX::parseError());




        $descs = $this->descR->findDesc($data->table->getCategorie());
        #dd($descs);
        #if($data->getDescName() !== "readFile")
        #dd($data);
        $tableId = $data->table->getId();
        if(!$xlsx->sheetName(1))//possibility of one big fiel
        {
            #if ($this->saveAll($data->getDescName(), $descs, $xlsx, $data->iteration, 0))
            if ($this->saveAll($descs, $xlsx, $data->iteration, 0, $tableId))
                $data->iteration = -1;
            else
                $data->iteration = $data->iteration + 1;
        }
        else
        {
            if (!$xlsx->sheetName($data->iteration))
                return null;
            $this->saveAll($descs, $xlsx, 0, $data->iteration, $tableId);

            $data->iteration = $data->iteration + 1;
            if (!$xlsx->sheetName($data->iteration))
                $data->iteration = -1;
        }
        return ($data);

    }
    private function saveAll($descs, $xlsx, $iteration, $sheetInc, $tableId)
    {
        $name = $xlsx->sheetName($sheetInc);
        if (!($desc = $this->getDesc($name, $descs)))
            dd("name of sheet '".$name."' don't have correspondance in description, TODO replace this message");
        dump($desc);
        if (!($meta = $this->verifyMetaData($desc, $xlsx, $sheetInc)))
            dd("your description and your data of '".$name."' don't correpond, change message");
        dump($meta);

        return $this->saveData($iteration, $xlsx, $meta, $sheetInc, $desc, $tableId);
    }


    //=====================select desc in table================================
    private function getDesc($dataName, $descs)
    {
        for ($inc = 0; $inc <= sizeof($descs) - 1; $inc++)
            if ($descs[$inc]->getName() === $dataName)
                return $descs[$inc];
        return null;
        //retrive descSelect

    }

    //=====================meta name================================
    private function verifyMetaData($desc, $xlsx, $sheetInc)
    {
        $attributPlace = [];
        foreach ($desc->getDescriptionArray() as $colName => $colType)
        {
            $place = 0;
            $exist = false;
            foreach ($xlsx->rows($sheetInc, 1)[0] as $colData)
            {
                if ($colData === $colName)
                {
                    $attributPlace[$colData] = $place;
                    $exist = true;
                    //dump($colData);
                    break;
                }
                $place++;
            }
            if (!$exist)
                return null;
        }
        return $attributPlace;


        dump($attributPlace);

        //$result = $this->dynaR->saveInTable($data->data, $descs[$descSelect], $uriVariables["id"], true);
        //$result = $this->dynaR->saveInTable($data->data, $descs[$descSelect], $uriVariables["id"], true);

        //retrive attributPlace
        //
        //

    }

    //=====================save data================================
    private function saveData($iteration, $xlsx, $attributPlace, $sheetInc, $desc, $tableId)
    {
        $constDataInterval = 1000;
        $constChunkInterval = 20;
        #$firstPace = true;
        $dataInterval = 0;
        $chunkInterval = 0;
        $skipData = $constDataInterval * $constChunkInterval * $iteration + 1;
        dump(memory_get_usage());
        $dataSave = [];
        #foreach ($xlsx->rows() as $r)
        #dd($data->iteration);

        dump($attributPlace);

        dump($sheetInc);
        foreach ($xlsx->readRows($sheetInc) as $r)
        {
            if ($skipData)
            {
                dump($r);
                $skipData--;
                continue;
            }

            foreach($attributPlace as $key => $val)
            {
                $dataSave[$key] = $r[$val];
            }
            #dump($dataSave);
            $this->dynaR->saveInTable($dataSave, $desc, $tableId, false, $dataInterval);

            #dump(memory_get_usage());
            $dataInterval++;
            if ($dataInterval == $constDataInterval)
            {
                $dataInterval = 0;
                $chunkInterval++;
                $this->dynaR->flush();
                if ($chunkInterval == $constChunkInterval)
                    return false;
                #dd("asd");
            }


        }
        $this->dynaR->flush();
        //===================================================================
        return true;
    }
}
