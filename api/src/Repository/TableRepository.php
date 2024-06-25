<?php

namespace App\Repository;

use Doctrine\DBAL\Types\Type;
use App\Entity\Table;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\SchemaTool;
use App\Entity\desc;
use App\Entity\TableDynamique;
use Symfony\Bundle\SecurityBundle\Security;

/**
 * @extends ServiceEntityRepository<Table>
 *
 * @method Table|null find($id, $lockMode = null, $lockVersion = null)
 * @method Table|null findOneBy(array $criteria, array $orderBy = null)
 * @method Table[]    findAll()
 * @method Table[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TableRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private Security $security)
    {
        parent::__construct($registry, Table::class);
    }

    private function getMetadataDesc($desc)
    {
        $meta = ($this->getClassMetadata());//get the class, todo change the base class
        dump($meta);

            $meta->setSequenceGeneratorDefinition(array('sequenceName'   => $desc->getName()."_id_sequence"));//maybe use custom?? change the sequence ??the thing who inc id??

            
            //$meta->setIdentifierValues($entity, array('name' => '???'));
            foreach($desc->getDescriptionArray() as $name => $type)
            {

                $tmp = array(
                  "fieldName" => $name,
                  "columnName" => $name,
                );
                  if ($type === "id" or $type === "uid")
                  {
                    $tmp["type"] = ($type === "id") ? "integer" : "guid";
                    $tmp["id"] = true;
                  }
                else
                    $tmp["type"] = $type;
                $meta->addInheritedFieldMapping($tmp);//add new column
            }
            $meta->setTableName($desc->getCategorie()."_".$desc->getName());//the name of table need to be differente
            dump($meta);
            return $meta;

    }

    public function updateGlobalBoolean(string $idTable, bool $Bool, string $option="partagePublic")
    {
        dd("probably useless");
        dump("asd");
        $this->createQueryBuilder('d')
            ->update()
            ->set('d.'.$option, ':'.$option)
            ->andWhere('d.id = :val')
            ->setParameter($option, $Bool)
            ->setParameter('val', $idTable)
            ->getQuery()
            ->execute()
        ;
        $this->getEntityManager()->flush();
    }

    public function getItem(string $id)
    {
        if (!$result = $this->find($id))
            return NULL;
        return $result;
        
        if (!$result = $this->find($id))
            return NULL;

        $isPublic = $result->getPartagePublic();
        $user = $this->security->getUser();
        if (in_array($id, $user->getTables()) or
            in_array($id, $user->getRelatedTables()) or
            $isPublic)
            return $result;
        return NULL;
    }

    public function save(Table $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Table $entity, bool $flush = false): void
    {
        dump("remove");
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
