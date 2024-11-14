<?php

namespace App\Repository;

use App\Entity\Desc;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @extends ServiceEntityRepository<Desc>
 *
 * @method Desc|null find($id, $lockMode = null, $lockVersion = null)
 * @method Desc|null findOneBy(array $criteria, array $orderBy = null)
 * @method Desc[]    findAll()
 * @method Desc[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DescRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Desc::class);
    }

    public function isValid(array $array) //link TableDynamiqueRepository.php
    {
        $text = 0;
        $integer = 0;
        $float = 0;
        $date = 0;
        $allType = ["text", "integer", "date", "float"];
        $inc = 0;
        foreach($array as $name => $type)
        {
            dump($name);
            if (!preg_match('/^[a-zA-Z0-9_]+$/', $name))
                throw new HttpException(422, $name." need to be constitued of leter or underscore");
            if (!is_string($type))
                throw new HttpException(422, $type." need to be a string");
            if ($type === "id")
                continue;
            if (in_array($type, $allType))
                ++$inc;
            else
                throw new HttpException(422, $type." is not a valid type, use text or integer");
            if ($inc > 20)
                throw new HttpException(422, "not more than 20 variables");
        }
    }

    public function save(Desc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Desc $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function updateGlobalBoolean(string $categorie, bool $Bool, string $option="partagePublic") //nombre de description appartenant a une categorie
    {
        dump("asd");
        $this->createQueryBuilder('d')
            ->update()
            ->set('d.'.$option, ':'.$option)
            ->andWhere('d.categorie = :val')
            ->setParameter($option, $Bool)
            ->setParameter('val', $categorie)
            ->getQuery()
            ->execute()
        ;
        $this->getEntityManager()->flush();
    }

    public function getNumberOfPerm($value) //nombre de description appartenant a une categorie
    {
        return $this->createQueryBuilder('d')
            ->select('MAX(d.permNumber)')
            ->andWhere('d.categorie = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findDoublon($categorie, $name): ?Desc
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.categorie = :val1')
            ->andWhere('d.name = :val2')
            ->setParameter('val1', $categorie)
            ->setParameter('val2', $name)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function findDesc(mixed $categorie, ?string $name = null)
    {

        if ($name)
            $result = $this->findby(["categorie" => $categorie, 'name' => $name]);
        else
            $result = $this->findby(["categorie" => $categorie]);
        return $result;
    }
}
