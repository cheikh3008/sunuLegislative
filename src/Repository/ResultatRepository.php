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

    public function findByCirconscription()
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT  D.nom , SUM(R.nbVotant) as nbVotant , SUM(R.bulletinNull) as bulletinNull, SUM(R.bulletinExp)  as bulletinExp
                FROM App\Entity\Resultat R, App\Entity\BureauVote B, App\Entity\User U, App\Entity\Departement D
                WHERE D.id = B.commune AND U.BV = B.id AND R.user = U.id  
                GROUP BY D.nom
             "
            )->getResult();
    }

    public function findByCommune()
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT  D.nom, D.commune , SUM(R.nbVotant) as nbVotant , SUM(R.bulletinNull) as bulletinNull, SUM(R.bulletinExp)  as bulletinExp
                FROM App\Entity\Resultat R, App\Entity\BureauVote B, App\Entity\User U, App\Entity\Departement D
                WHERE D.id = B.commune AND U.BV = B.id AND R.user = U.id  
                GROUP BY D.nom, D.commune
             "
            )->getResult();
    }

    public function findNombreTotalVoix()
    {
        return $this->getEntityManager()
            ->createQuery(
                " SELECT C.nom, SUM(CR.nombre) as nbVoix
                FROM App\Entity\Resultat R, App\Entity\Coalition C, App\Entity\ResultatCoalition CR
                WHERE C.id = CR.coaltion AND R.id = CR.resulat
                GROUP BY C.nom
                 "
            )->getResult();
    }

    public function findNombreResultBureauVoteParDepartement()
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT D.nom,  COUNT(R.bulletinExp) as NBresultat
                FROM  App\Entity\BureauVote B,  App\Entity\Resultat R,  App\Entity\User U, App\Entity\Departement  D
                WHERE R.user = U.id AND B.id = U.BV AND D.id = B.commune
                GROUP BY D.nom
                "
            )->getResult();
    }

    public function findNombreBureauVoteCoalition()
    {
        return $this->getEntityManager()
            ->createQuery(
                " SELECT D.nom, COUNT(B.nomBV) as NBresultat
                FROM  App\Entity\BureauVote B, App\Entity\Departement D
                WHERE  D.id = B.commune
                GROUP BY D.nom
                 "
            )->getResult();
    }

    public function findNombreResultBureauVoteCoalition()
    {
        return $this->getEntityManager()
            ->createQuery(
                " SELECT 
                    B.nomBV , COUNT (R.bulletinExp) as NBresultat
                    FROM  App\Entity\BureauVote B,  App\Entity\Resultat R,  App\Entity\User U
                    WHERE R.user = U.id AND B.id = U.BV
                    GROUP BY B.nomBV
                 "
            )->getResult();
    }

    public function findNombreBureauVoteTotal()
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT COUNT(B.nomBV) as nbBV, SUM(B.nbElecteur) as nbElecteur 
                FROM  App\Entity\BureauVote B 
                "
            )->getResult();
    }

    public function findTotalNbresultatParDepartement()
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT D.nom as d_nom, C.nom, SUM(RC.nombre) as nombre
                FROM App\Entity\ResultatCoalition RC, App\Entity\Coalition C, App\Entity\User U,  App\Entity\Resultat R, App\Entity\Departement D 
                WHERE RC.resulat= R.id AND RC.coaltion= C.id AND R.user= U.id AND D.id = U.commune  
                GROUP BY D.nom, C.nom   ORDER BY C.nom
                "
            )->getResult();
    }

    public function findTotalNbresultatParCommune()
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT D.commune as d_nom, C.nom, SUM(RC.nombre) as nombre
                FROM App\Entity\ResultatCoalition RC, App\Entity\Coalition C, App\Entity\User U,  App\Entity\Resultat R, App\Entity\Departement D 
                WHERE RC.resulat= R.id AND RC.coaltion= C.id AND R.user= U.id AND D.id = U.commune  
                GROUP BY D.commune, C.nom   ORDER BY C.nom
                "
            )->getResult();
    }

    public function findNombreTotalElecteursDepartement()
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT D.nom , SUM(B.nbElecteur) as nbElecteur 
                FROM  App\Entity\Departement D ,  App\Entity\BureauVote B  
                WHERE D.id = B.commune
                GROUP BY D.nom 
                "
            )->getResult();
    }

    public function findNombreTotalElecteursCommune()
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT D.commune , SUM(B.nbElecteur) as nbElecteur 
                FROM  App\Entity\Departement D ,  App\Entity\BureauVote B  
                WHERE D.id = B.commune
                GROUP BY D.commune
                "
            )->getResult();
    }

    // SELECT departement.nom ,coalition.nom, SUM(resultat_coalition.nombre) FROM resultat, resultat_coalition, coalition, user, departement WHERE resultat_coalition.resulat_id = resultat.id AND resultat_coalition.coaltion_id = coalition.id AND resultat.user_id = user.id AND departement.id = user.commune_id  GROUP BY departement.nom, coalition.nom
    // SELECT BV.nom_cir, COUNT(BV.nom_bv), COUNT(R.bulletin_exp) FROM resultat R, bureau_vote BV, user U WHERE R.user_id = U.id AND BV.id = U.bv_id GROUP BY BV.nom_cir

    // SELECT  r.user_id , bureau_vote.nom_cir , retenus.nom , SUM(r.nb_inscrit), SUM(r.nb_votant), SUM(r.bulletin_null), SUM(r.bulletin_exp)  FROM resultat as r, bureau_vote, user, retenus WHERE r.retenus_id = retenus.id AND bureau_vote.id = user.bv_id and r.user_id = user.id   GROUP BY r.user_id, bureau_vote.nom_cir, retenus.nom

    // SELECT   bureau_vote.nom_cir ,  SUM(r.nb_inscrit), SUM(r.nb_votant), SUM(r.bulletin_null), SUM(r.bulletin_exp)  FROM resultat as r, bureau_vote, user, retenus WHERE r.retenus_id = retenus.id AND bureau_vote.id = user.bv_id and r.user_id = user.id  GROUP BY  bureau_vote.nom_cir

    // SELECT retenus.nom, SUM(resultat.bulletin_exp) as nbVoix FROM resultat, retenus WHERE retenus.id = resultat.retenus_id GROUP BY retenus.nom

    // SELECT b.nom_cir , COUNT(b.nom_bv) FROM  bureau_vote as b GROUP BY b.nom_cir

    // SELECT departement.nom , SUM(bureau_vote.nb_electeur) FROM departement, bureau_vote WHERE departement.id = bureau_vote.commune_id GROUP BY departement.nom
}
