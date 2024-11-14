<?php

namespace App\Repository;

use ReflectionProperty;
use App\Entity\TableDynamique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Desc;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Id\IdentityGenerator;
use Doctrine\ORM\Query\ResultSetMapping;
use App\Filter\RangeFilter;
use App\Filter\SearchFilter;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @extends ServiceEntityRepository<TableDynamique>
 *
 * @method TableDynamique|null find($id, $lockMode = null, $lockVersion = null)
 * @method TableDynamique|null findOneBy(array $criteria, array $orderBy = null)
 * @method TableDynamique[]    findAll()
 * @method TableDynamique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TableDynamiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TableDynamique::class);
    }
    private $meta;
    private $limit = 1000;

    private function getMetadataDesc(Desc $desc, string $table_id, $meta)
    {
        $name_table = str_replace("-", "_", "name"."_".$table_id."_".$desc->getName());

        $meta->idGenerator = new IdentityGenerator();
        $meta->setSequenceGeneratorDefinition(array('sequenceName'   => $name_table."_id_sequence"));//maybe use custom?? change the sequence ??the thing who inc id??

        foreach($desc->getDescriptionArray() as $name => $type)
        {
            if ($type === "id" or $type === "uid")
                continue;
            $tmp = array(
                "fieldName" => $name,
                "columnName" => $name,
            );
            $tmp["type"] = $type;
            dump($tmp);
            $meta->addInheritedFieldMapping($tmp);//add new column
        }
        $meta->setPrimaryTable(array("name" => '`'.$name_table));//the name of table need to be differente
        #dump($meta);
        return $meta;

    }

    public function save(array $descs, string $table_uuid, bool $flush = false): void
    {

        $test = clone $this->getEntityManager();
        $meta = ($this->getClassMetadata());//get the class, todo change the base class
        foreach($descs as $number => $desc)
        {
            $metaS = array($this->getMetadataDesc($desc, $table_uuid, clone $meta));
            $schemaTool = new SchemaTool($this->getEntityManager());
            $schemaTool->createSchema(
                $metaS
            );    //add the table to database
        }
    }

    public function getAttr(desc $desc)//link DescRepository.php
    {
        /*
        $text = 0;
        $integer = 0;
        $float = 0;
        $date = 0;
         */
        $inc = 0;
        $ret = array();
        foreach($desc->getDescriptionArray() as $name => $type)
        {
            if ($type === "id")
                continue;
            $inc++;
            $ret[$name] = "var".$inc;
            /*$$type++;
            if ($type === "text") //type for doctrine
                $attr = "string";//name in Entity/php
            else if ($type === "integer")
                $attr = "integer";
            else if ($type === "date")
                $attr = "date";
            else if ($type === "float")
                $attr = "double";
            else
                dd("fuck");
            $attr.=$$type;
            $ret[$name] = $attr;
            */
        }
        return $ret;
    }


    private function _insertInTable($attr, $dyna, $data, $name, $meta)
    {
        #dump($data);
        #dd($attr);//integer1
        #dd($meta->fieldMappings[$name]["type"]);
        $test_type;
        switch ($meta->fieldMappings[$name]["type"])
        {
            case "integer":
                $dataCast = (int) ($data[$name]);
                $test_type = is_int($dataCast);
                if ($test_type && (-2147483648 > $dataCast || $dataCast > 2147483647))
                {
                    throw new HttpException(422, "Field ".$name." value must be between -2147483648 and 2147483647, value ".$dataCast." is entered");
                }
                $dyna->$attr = $dataCast;
                #$test_type = is_int($data[$name]);
                #if ($test_type && (-2147483648 > $data[$name] || $data[$name] > 2147483647))
                #    throw new HttpException(422, "Field ".$name." value must be between -2147483648 and 2147483647");
                #$dyna->$attr = $data[$name];
                break;
            case "text":
                #$test_type = is_string($data[$name]);
                $test_type = isset($data[$name]);
                $dyna->$attr = (string) $data[$name];
                break;
            case "float":
                $test_type = is_float($data[$name]) || is_integer($data[$name]);
                $dyna->$attr = $data[$name];
                break;
            case "date":
                $date = strtotime($data[$name]);
                if ($date < 1)
                    throw new HttpException(422, $name." are not correctly formated");
                $dyna->$attr = new \DateTime(date("Y-m-d h:i:sa", $date));
                $test_type = 1;
                break;
            default:
                dd($meta->fieldMappings[$name]["type"]);
        }

        if(!$test_type)
                throw new HttpException(422, $name." doesn't have the right type ".gettype($data[$name])." given.");
    }

    public function changeInTable(array $data, TableDynamique $oldData, desc $desc, array $uriVariables, bool $flush = false)
    {
        $dyna = new TableDynamique;
        $meta = $this->getMetadataDesc($desc, $uriVariables["id"], $this->getClassMetadata());

        foreach($this->getAttr($desc) as $name => $attr)
        {
            if (!isset($data[$name]))
                $dyna->$attr = $oldData->$attr;
            else
                $this->_insertInTable($attr, $dyna, $data, $name, $meta);

            $meta->reflFields[$name] = new ReflectionProperty("App\Entity\TableDynamique", $attr);
        }
        $dyna->setId($uriVariables["idInTable"]);
        $this->getEntityManager()->merge($dyna);
        if ($flush)
            $this->getEntityManager()->flush();
        unset($dyna);
        #return $dyna;
    }
    #private int $aaa = 0;

    public function saveInTable(array $data, desc $desc, string $id, bool $flush = false, int $interval = 0)
    {
        #dd($data);
        #if(!isset($this->dyna[$interval]))
        #if(isset($this->dyna[$interval]))
        #{
        #    #$this->dyna[$interval]->ResetObject();
        #    unset($this->dyna[$interval]);
        #    $this->dyna[$interval] = null;
        #}
        #if(!isset($this->dyna[$interval]))
        #$this->dyna[$interval] = new TableDynamique;
        #$dyna = $this->dyna[$interval];
        $dyna = new TableDynamique;
        #$dyna->setRelation_one(null);
        #dump($dyna);
        #if ($this->aaa === 5)
        #    dd("asd");
        #$this->aaa += 1;

        #$dyna->setId(null);

        #dd($dyna);

        #dd($this->getEntityManager());
        if (!isset($this->meta))
        {
            $this->meta = $this->getMetadataDesc($desc, $id, $this->getClassMetadata());
            foreach($this->getAttr($desc) as $name => $attr)
            {
                $this->meta->reflFields[$name] = new ReflectionProperty("App\Entity\TableDynamique", $attr);
            }
            #dump($this->meta);
        }


        foreach($this->getAttr($desc) as $name => $attr)
        {
            if (!isset($data[$name]))
                throw new HttpException(422, $name." is not defined");
            $this->_insertInTable($attr, $dyna, $data, $name, $this->meta);
        }

        #dump($this->dyna[0]);
        #dump($dyna);
        $this->getEntityManager()->persist($dyna);
        if ($flush)
            $this->flush();
        #$dyna = null;
        #dump($this->dyna[0]);
        return null;
        #return $dyna;
    }

    public function getById(desc $desc, string $table_uuid, int $id)
    {
        $meta = $this->getMetadataDesc($desc, $table_uuid, $this->getClassMetadata());

        foreach($this->getAttr($desc) as $name => $attr)
            $meta->reflFields[$name] = new ReflectionProperty("App\Entity\TableDynamique", $attr);

        $result = $this->find($id);
        dump($result);
        return ($result);
    }

    public function getCollection(desc $desc, string $table_uuid, array $context, int $permMethode)
    {
        $meta = $this->getMetadataDesc($desc, $table_uuid, $this->getClassMetadata());

        foreach($this->getAttr($desc) as $name => $attr)
            $meta->reflFields[$name] = new ReflectionProperty("App\Entity\TableDynamique", $attr);

        $filter = isset($context["filters"]) ? $context["filters"] : [];
            $result = $this->findbyfilter($filter, $meta, $permMethode);
        dump($result);
        return ($result);
    }

    public function remove(TableDynamique $entity, bool $flush = false): void
    {
        dump("remove");
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
    public function flush()
    {
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
    }

    //    /**
    //     * @return TableDynamique[] Returns an array of TableDynamique objects
    //     */
    public function findbyfilter(array $filter, $meta, int $permMethode)
    {
        $QNG = new QueryNameGenerator();
        $qb = $this->createQueryBuilder('p');
        $range = new RangeFilter();
        $search = new SearchFilter();
        $rand = 0;
        #$range->filterProperty('gt', "int2", 4, $qb, $QNG, 'p', context : $filter);
        $qb->setMaxResults($this->limit);
        foreach($filter as $key => $value)
        {
            dump($key);
            if ($key == "field")
            {
                dump("select");
                $values = explode(',', $value);
                $selectStr = "";
                foreach($values as $value)
                {
                    $value = trim($value);
                   if (!isset($meta->fieldMappings[$value]))
                    continue;
                   $selectStr = $selectStr.(($selectStr)?', ':'').sprintf("%s.%s", 'p', $value);
                }
                dump($selectStr);
                $qb->select($selectStr);//need to proctect
            }
            else if ($key == "rand")
                $rand = intval($value);
            else if ($key == "offset")
                $qb->setFirstResult($this->limit * $value);
            else
            {
                dump("where");
                dump($meta->fieldMappings);
                if (isset($meta->fieldMappings[$key]))
                {
                        $operator_value = explode(':', $value);
                        if (count($operator_value) != 2)
                            continue;
                        dump($value);
                        dump($operator_value);

                    if ($meta->fieldMappings[$key]["type"] == "text")
                    {
                        $search->addWhere($operator_value[0], $qb, $QNG, 'p', $key, $operator_value[1]);
                    }
                    else if ($meta->fieldMappings[$key]["type"] == "integer")
                    {
                        $range->AddWhere($qb, $QNG, 'p', $key, $operator_value[0], $operator_value[1]);
                    }
                }
            }
        }
        #$qb->orderBy('t_int1');
        if ($permMethode == 1) //get
            $result = $qb->getQuery()->getArrayResult();
        else
            $result = $qb->getQuery()->getResult();
        if ($rand > 1)
        {
            $tmp = [];
            foreach (array_rand($result, (min($rand, count($result)))) as $key)
                $tmp[] = $result[$key];
            $result = $tmp;
        }
        #dd($result);
        #dd($this->makeDynamique($result));
        return $result;
    }

    //    /**
    //     * @return TableDynamique[] Returns an array of TableDynamique objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TableDynamique
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
