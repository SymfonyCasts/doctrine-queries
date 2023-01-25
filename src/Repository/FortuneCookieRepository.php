<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\FortuneCookie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FortuneCookie>
 *
 * @method FortuneCookie|null find($id, $lockMode = null, $lockVersion = null)
 * @method FortuneCookie|null findOneBy(array $criteria, array $orderBy = null)
 * @method FortuneCookie[]    findAll()
 * @method FortuneCookie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FortuneCookieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FortuneCookie::class);
    }

    public function countNumberPrintedForCategory(Category $category): array
    {
        $conn = $this->getEntityManager()->getConnection();
//        dd($conn);
        $sql = 'SELECT SUM(fortune_cookie.number_printed) AS fortunesPrinted, AVG(fortune_cookie.number_printed) fortunesAverage, category.name FROM fortune_cookie INNER JOIN category ON category.id = fortune_cookie.category_id WHERE fortune_cookie.category_id = :category GROUP BY category.id, category.name';
        $stmt = $conn->prepare($sql);
        $result = $stmt->executeQuery([
            'category' => $category->getId(),
        ]);
//        dd($result->fetchAssociative());
        return $result->fetchAssociative();

//        $result = $this->createQueryBuilder('fortune_cookie')
//            ->select('SUM(fortune_cookie.numberPrinted) AS fortunesPrinted')
//            ->addSelect('AVG(fortune_cookie.numberPrinted) fortunesAverage')
//            ->addSelect('category.name')
//            ->innerJoin('fortune_cookie.category', 'category')
//            ->andWhere('fortune_cookie.category = :category')
//            ->setParameter('category', $category)
//            ->getQuery()
//            ->getSingleResult();
//
////        dd($result);
//
////        return (int) $result;
//        return $result;
    }

    public function save(FortuneCookie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(FortuneCookie $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public static function createContinuedCriteria(): Criteria
    {
        return Criteria::create()
            ->andWhere(Criteria::expr()->eq('discontinued', false));
    }

//    /**
//     * @return FortuneCookie[] Returns an array of FortuneCookie objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('f.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?FortuneCookie
//    {
//        return $this->createQueryBuilder('f')
//            ->andWhere('f.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
