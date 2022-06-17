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

    // public function findOneBySomeField(): array
    // {
    //     return $this->createQueryBuilder('r')
    //         ->select('SUM(r.wallu) as \'wallu senegal\'')
    //         ->getQuery()
    //         ->getResult();
    // }

    public function findByDepartement()
    {
        return $this->getEntityManager()
            ->createQuery(
                ' SELECT B.nomCir,  
                SUM(R.nbVotant) as nbVotant,
                SUM(R.bulletinNull) as bulletinNull,
                SUM(R.bulletinExp) as bulletinExp,
                SUM(R.wallu) as wallu,
                SUM(R.yewi) as yewi,
                SUM(R.bby) as bby,
                SUM(R.aar) as aar,
                SUM(R.natangue) as natangue,
                SUM(R.serviteur) as serviteur,
                SUM(R.bokkgisgis) as bokkgisgis,
                SUM(R.ucb) as ucb
                    FROM App\Entity\User U,
                    App\Entity\Resultat R,
                    App\Entity\BureauVote B
                    WHERE  B.id = U.BV
                    AND R.user = U.id  
                    GROUP BY B.nomCir  '
            )->getResult();
    }

    public function findNombreTotalVoix()
    {
        return $this->getEntityManager()
            ->createQuery(
                " SELECT 
                SUM(R.wallu) as wallu,
                SUM(R.yewi) as yewi,
                SUM(R.bby) as bby,
                SUM(R.aar) as aar,
                SUM(R.natangue) as natangue,
                SUM(R.serviteur) as serviteur,
                SUM(R.bokkgisgis) as bokkgisgis,
                SUM(R.ucb) as ucb
                FROM  App\Entity\Resultat R "
            )->getResult();
    }

    public function findNombreTotalVoixCoalition()
    {
        return $this->getEntityManager()
            ->createQuery(
                " SELECT 
                SUM(R.wallu) as Wallu_Senegal,
                SUM(R.yewi) as Yewi_Askan_Wi,
                SUM(R.bby) as Benno_Bokk_Yakaar,
                SUM(R.aar) as Alternativespour_une_Assemblee_de_rupture,
                SUM(R.natangue) as Naatangue_Senegal,
                SUM(R.serviteur) as Les_serviteurs,
                SUM(R.bokkgisgis) as Bokk_Guis_Guis,
                SUM(R.ucb) as Union_citoyenne_Bunt_Bi
                FROM  App\Entity\Resultat R "
            )->getResult();
    }

    public function findNombreBureauVoteCoalition()
    {
        return $this->getEntityManager()
            ->createQuery(
                " SELECT 
                    B.nomCir , COUNT (B.nomBV) as nbBureauVote
                    FROM  App\Entity\BureauVote B  GROUP BY B.nomCir
                 "
            )->getResult();
    }

    // SELECT  r.user_id , bureau_vote.nom_cir , retenus.nom , SUM(r.nb_inscrit), SUM(r.nb_votant), SUM(r.bulletin_null), SUM(r.bulletin_exp)  FROM resultat as r, bureau_vote, user, retenus WHERE r.retenus_id = retenus.id AND bureau_vote.id = user.bv_id and r.user_id = user.id   GROUP BY r.user_id, bureau_vote.nom_cir, retenus.nom

    // SELECT   bureau_vote.nom_cir ,  SUM(r.nb_inscrit), SUM(r.nb_votant), SUM(r.bulletin_null), SUM(r.bulletin_exp)  FROM resultat as r, bureau_vote, user, retenus WHERE r.retenus_id = retenus.id AND bureau_vote.id = user.bv_id and r.user_id = user.id  GROUP BY  bureau_vote.nom_cir

    // SELECT retenus.nom, SUM(resultat.bulletin_exp) as nbVoix FROM resultat, retenus WHERE retenus.id = resultat.retenus_id GROUP BY retenus.nom

    // SELECT b.nom_cir , COUNT(b.nom_bv) FROM  bureau_vote as b GROUP BY b.nom_cir
}
