<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry, private UserPasswordHasherInterface $userPasswordEncoder)
    {
        parent::__construct($registry, User::class);
    }
    public function aaa()
    {
        return "asd";
    }

    public function addTables(User $entity, string $desc, bool $proprietaire): void
    {
        #$entity = $this->getAdmin($entity);
        if ($proprietaire)
            $entity->addTable($desc);
        else
            $entity->addRelatedTable($desc);

        dump($entity);
        $this->getEntityManager()->merge($entity);
        $this->getEntityManager()->flush();
    }

    public function getRelated($related, $id)
    {
        $qb = $this->createQueryBuilder('user');
        return $qb->orWhere('user.related'.$related.' like :related')
                  ->setParameter('related', '%'.$id.'%')
                  ->getQuery()
                  ->getResult();
    }

    public function addDescs(User $entity, string $desc, bool $proprietaire): void
    {
        #$entity = $this->getAdmin($entity);
        if ($proprietaire)
            $entity->addDesc($desc);
        else
            $entity->addRelatedDesc($desc);

        dump($entity);
        $this->getEntityManager()->merge($entity);
        $this->getEntityManager()->flush();
    }

    public function getAdmin(User $entity): User
    {
        dd("probably deprecated, see role hierachy");
        $orga = $entity->getOrganisme();
        $role = $entity->getRoles()[0];
        if ($role == "ROLE_cap3c_support_tech")
        {
            if($orga == "cap3c")
                throw new HttpException(401, "change organisme");
            else
                return ($this->findAdmin($orga));
        }
        if ($role == "ROLE_orga_user")
            throw new HttpException(401, "you can't make this action");
        return $entity;
    }

    public function save(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function hash(User $user, string $password)
    {
        return($this->userPasswordEncoder->hashPassword($user, $password));
    }
    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    public function findAdmin($value): ?User
    {
        $result = $this->createQueryBuilder('u')
            ->andWhere('u.organisme = :val2')
            ->setParameter('val2', $value)
            ->getQuery()
            ->getResult()
        ;
        foreach($result as $userToVerify)
        {
            if ($userToVerify->getRoles()[0] == "ROLE_orga_admin")
                return $user;
        }

        throw new HttpException(500, "admin not found");
    }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
