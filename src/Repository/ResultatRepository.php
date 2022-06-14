<?php

namespace App\Repository;

use App\Entity\Resultat;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Resultat>
 *
 * @method Resultat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Resultat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Resultat[]    findAll()
 * @method Resultat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ResultatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Resultat::class);
    }

    public function add(Resultat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Resultat $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    //    /**
    //     * @return Resultat[] Returns an array of Resultat objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    public function findOneBySomeField($value): array
    {
        return $this->createQueryBuilder('r.id')
            ->andWhere('r.retenus = :val')
            ->getQuery()
            ->getResult();
    }

    public function findByDepartement()
    {
        return $this->getEntityManager()
            ->createQuery(
                ' SELECT B.nomCir,  SUM(R.nbInscrit) as nbInscrit, SUM(R.nbVotant) as nbVotant, SUM(R.bulletinNull) as bulletinNull, SUM(R.bulletinExp) as bulletinExp
                    FROM App\Entity\User U, 
                    App\Entity\Retenus RE, 
                    App\Entity\Resultat R,
                    App\Entity\BureauVote B
                    WHERE R.retenus = RE.id 
                    AND B.id = U.BV
                    AND R.user = U.id  
                    GROUP BY B.nomCir  '
            )->getResult();
    }
    // SELECT  r.user_id , bureau_vote.nom_cir , retenus.nom , SUM(r.nb_inscrit), SUM(r.nb_votant), SUM(r.bulletin_null), SUM(r.bulletin_exp)  FROM resultat as r, bureau_vote, user, retenus WHERE r.retenus_id = retenus.id AND bureau_vote.id = user.bv_id and r.user_id = user.id   GROUP BY r.user_id, bureau_vote.nom_cir, retenus.nom
   
    // SELECT   bureau_vote.nom_cir ,  SUM(r.nb_inscrit), SUM(r.nb_votant), SUM(r.bulletin_null), SUM(r.bulletin_exp)  FROM resultat as r, bureau_vote, user, retenus WHERE r.retenus_id = retenus.id AND bureau_vote.id = user.bv_id and r.user_id = user.id  GROUP BY  bureau_vote.nom_cir
}
