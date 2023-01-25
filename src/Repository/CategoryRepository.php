<?php

namespace App\Repository;

use App\Entity\Category;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Category>
 *
 * @method Category|null find($id, $lockMode = null, $lockVersion = null)
 * @method Category|null findOneBy(array $criteria, array $orderBy = null)
 * @method Category[]    findAll()
 * @method Category[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Category::class);
    }

    public function findAllOrdered(): array
    {
//        $dql = 'SELECT category FROM App\Entity\Category as category ORDER BY category.name DESC';
//        $query = $this->getEntityManager()->createQuery($dql);
////        dd($query->getSQL());

        $qb = $this->createQueryBuilder('category');
        $this->addFortuneCookieJoinAndSelect($qb);
        $this->addSelectAndGroupByCategory($qb);
        $this->addOrderByCategoryName($qb);
        $query = $qb->getQuery();
//        dd($query->getDQL());

        return $query->execute();
    }

    public function search(string $term): array
    {
        $qb = $this->createQueryBuilder('category');
        $this->addFortuneCookieJoinAndSelect($qb);
        $this->addSelectAndGroupByCategory($qb);
        $this->addOrderByCategoryName($qb);

        $termList = explode(' ', $term);

        return $qb
            ->andWhere('category.name LIKE :searchTerm
            OR category.name IN (:termList)
            OR category.iconKey LIKE :searchTerm
            OR fortune_cookies.fortune LIKE :searchTerm')
            ->setParameter('termList', $termList)
            ->setParameter('searchTerm', '%'.$term.'%')
            ->getQuery()
            ->execute();
    }

    public function findWithFortunesJoin(int $id): ?Category
    {
        $qb = $this->createQueryBuilder('category');
        $this->addFortuneCookieJoinAndSelect($qb);

        return $qb
            ->andWhere('category.id = :id')
            ->setParameter('id', $id)
            ->orderBy('RAND()', Criteria::ASC)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Category $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    private function addFortuneCookieJoinAndSelect(QueryBuilder $qb): QueryBuilder
    {
        return $qb->leftJoin('category.fortuneCookies', 'fortune_cookies')
            ->addSelect('fortune_cookies');
    }

    private function addOrderByCategoryName(QueryBuilder $qb): QueryBuilder
    {
        return $qb->addOrderBy('category.name', Criteria::DESC);
    }

    private function addSelectAndGroupByCategory(QueryBuilder $qb): QueryBuilder
    {
        return $qb->select('category.id, category.name, category.iconKey, COUNT(fortune_cookies.id) AS fortuneCookiesTotal')
            ->groupBy('category.id')
            ->addGroupBy('category.name')
            ->addGroupBy('category.iconKey');
    }

//    /**
//     * @return Category[] Returns an array of Category objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Category
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
