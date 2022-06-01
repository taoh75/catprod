<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends ServiceEntityRepository<Product>
 *
 * @method Product|null find($id, $lockMode = null, $lockVersion = null)
 * @method Product|null findOneBy(array $criteria, array $orderBy = null)
 * @method Product[]    findAll()
 * @method Product[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductRepository extends ServiceEntityRepository
{
    public const PAGINATOR_PER_PAGE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function add(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Product $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getProductPaginator(int $offset, Request $req): Paginator
    {
        $query = $this->createQueryBuilder('c');
        if ($req->get('name') !='') {
            $vname=explode("|",$req->get('name'));
            foreach ($vname as $index => $value){
                $query->andWhere('c.name LIKE :searchName'.$index);
                $query->orWhere('c.code LIKE :searchName'.$index);
                $query->orWhere('c.description LIKE :searchName'.$index);
                $query->orWhere('c.brand LIKE :searchName'.$index);
                $query->setParameter('searchName'.$index, '%'.trim($value).'%');
            }
        }
        if ($req->get('category') > 0) {
            $query->andWhere('c.category = :searchCat');
            $query->setParameter('searchCat', $req->get('category'));
        }
        if ($req->get('active') > 0) {
            $query->andWhere('c.active = :searchActive');
            $query->setParameter('searchActive', (($req->get('active') == 1)?'1':'0'));
        }
        $query->orderBy('c.'.($req->get('orderby')??'id'), ($req->get('orn')??'DESC'));
        $query->setMaxResults(self::PAGINATOR_PER_PAGE);
        $query->setFirstResult($offset);
        $query->getQuery();
        return new Paginator($query);
    }

    public function getProductExport(Request $req): Array
    {
        $query = $this->createQueryBuilder('c');
        if ($req->get('name') !='') {
            $vname=explode("|",$req->get('name'));
            foreach ($vname as $index => $value){
                $query->andWhere('c.name LIKE :searchName'.$index);
                $query->orWhere('c.code LIKE :searchName'.$index);
                $query->orWhere('c.description LIKE :searchName'.$index);
                $query->orWhere('c.brand LIKE :searchName'.$index);
                $query->setParameter('searchName'.$index, '%'.trim($value).'%');
            }
        }
        if ($req->get('category') > 0) {
            $query->andWhere('c.category = :searchCat');
            $query->setParameter('searchCat', $req->get('category'));
        }
        if ($req->get('active') > 0) {
            $query->andWhere('c.active = :searchActive');
            $query->setParameter('searchActive', (($req->get('active') == 1)?'1':'0'));
        }
        $query->orderBy('c.'.($req->get('orderby')??'id'), ($req->get('orn')??'DESC'));
        $prods = $query->getQuery();
        return $prods->execute();
    }

//    /**
//     * @return Product[] Returns an array of Product objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Product
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
