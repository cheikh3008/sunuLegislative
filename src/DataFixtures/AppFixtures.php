<?php

namespace App\DataFixtures;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager): void
    {
        $role_representant = new Role();
        $role_representant->setLibelle('ROLE_REPRESENTANT');
        $manager->persist($role_representant);
        #### Ajout un admin system ######
        $role_admin = new Role();
        $role_admin->setLibelle("ROLE_ADMIN");
        $manager->persist($role_admin);

        $role_journaliste = new Role();
        $role_journaliste->setLibelle("ROLE_JOURNALISTE");
        $manager->persist($role_journaliste);

        $role_READER = new Role();
        $role_READER->setLibelle("ROLE_READER");
        $manager->persist($role_READER);

        // Super Admin
        $user = new User();
        $user->setUsername('cheikh3008')
            ->setPassword($this->encoder->hashPassword($user, "admin123"))
            ->setNom('Dieng')
            ->setPrenom('Cheikh')
            ->setUuid('admin123')
            ->setRole($role_admin)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(221773043248);
        $manager->persist($user);

        // Admin
        $user1 = new User();
        $user1->setUsername('administrateur')
            ->setPassword($this->encoder->hashPassword($user1, "election@2022"))
            ->setNom('Thiam')
            ->setPrenom('Ousseynou')
            ->setUuid('election@2022')
            ->setRole($role_admin)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(221784387796);
        $manager->persist($user1);

        // Reader
        $user2 = new User();
        $user2->setUsername('user')
            ->setPassword($this->encoder->hashPassword($user2, "user@2022"))
            ->setNom('Thiam')
            ->setPrenom('Ousseynou')
            ->setUuid('user@2022')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(221784387796);
        $manager->persist($user2);
        // Ajoult
        $leraltv = new User();
        $leraltv->setUsername('leraltv')
            ->setPassword($this->encoder->hashPassword($leraltv, "1111"))
            ->setNom('leraltv')
            ->setPrenom('leraltv')
            ->setUuid('1111')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(1111);
        $manager->persist($leraltv);

        // dakarbuzz
        $dakarbuzz = new User();
        $dakarbuzz->setUsername('dakarbuzz')
            ->setPassword($this->encoder->hashPassword($dakarbuzz, "1212"))
            ->setNom('dakarbuzz')
            ->setPrenom('dakarbuzz')
            ->setUuid('1212')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(1212);
        $manager->persist($dakarbuzz);

        // dakaractu
        $dakaractu = new User();
        $dakaractu->setUsername('dakaractu')
            ->setPassword($this->encoder->hashPassword($dakaractu, "1313"))
            ->setNom('dakaractu')
            ->setPrenom('dakaractu')
            ->setUuid('1313')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(1313);
        $manager->persist($dakaractu);

        // senego
        $senego = new User();
        $senego->setUsername('senego')
            ->setPassword($this->encoder->hashPassword($senego, "1414"))
            ->setNom('senego')
            ->setPrenom('senego')
            ->setUuid('1414')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(1414);
        $manager->persist($senego);

        // senenews
        $senenews = new User();
        $senenews->setUsername('senenews')
            ->setPassword($this->encoder->hashPassword($senenews, "1515"))
            ->setNom('senenews')
            ->setPrenom('senenews')
            ->setUuid('1515')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(1515);
        $manager->persist($senenews);

        // seneweb
        $seneweb = new User();
        $seneweb->setUsername('seneweb')
            ->setPassword($this->encoder->hashPassword($seneweb, "1616"))
            ->setNom('seneweb')
            ->setPrenom('seneweb')
            ->setUuid('1616')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(1616);
        $manager->persist($seneweb);

        // dmedia
        $dmedia = new User();
        $dmedia->setUsername('dmedia')
            ->setPassword($this->encoder->hashPassword($dmedia, "1717"))
            ->setNom('dmedia')
            ->setPrenom('dmedia')
            ->setUuid('1717')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(1717);
        $manager->persist($dmedia);

        // 2stv
        $stv = new User();
        $stv->setUsername('2stv')
            ->setPassword($this->encoder->hashPassword($stv, "1818"))
            ->setNom('2stv')
            ->setPrenom('2stv')
            ->setUuid('1818')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(1818);
        $manager->persist($stv);

        // gfm
        $gfm = new User();
        $gfm->setUsername('gfm')
            ->setPassword($this->encoder->hashPassword($gfm, "1919"))
            ->setNom('gfm')
            ->setPrenom('gfm')
            ->setUuid('1919')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(1919);
        $manager->persist($gfm);

        // walfadjri
        $walfadjri = new User();
        $walfadjri->setUsername('walfadjri')
            ->setPassword($this->encoder->hashPassword($walfadjri, "2020"))
            ->setNom('walfadjri')
            ->setPrenom('walfadjri')
            ->setUuid('2020')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(2020);
        $manager->persist($walfadjri);

        // 7tv
        $tv = new User();
        $tv->setUsername('7tv')
            ->setPassword($this->encoder->hashPassword($tv, "2121"))
            ->setNom('7tv')
            ->setPrenom('7tv')
            ->setUuid('2121')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(2121);
        $manager->persist($tv);

        // dtv
        $dtv = new User();
        $dtv->setUsername('dtv')
            ->setPassword($this->encoder->hashPassword($dtv, "2222"))
            ->setNom('dtv')
            ->setPrenom('dtv')
            ->setUuid('2222')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(2222);
        $manager->persist($dtv);

        // rts
        $rts = new User();
        $rts->setUsername('rts')
            ->setPassword($this->encoder->hashPassword($rts, "2323"))
            ->setNom('rts')
            ->setPrenom('rts')
            ->setUuid('2323')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(2323);
        $manager->persist($rts);

        // emedia
        $emedia = new User();
        $emedia->setUsername('emedia')
            ->setPassword($this->encoder->hashPassword($emedia, "2424"))
            ->setNom('emedia')
            ->setPrenom('emedia')
            ->setUuid('2424')
            ->setRole($role_READER)
            ->setCode('SN')
            ->setCommune(null)
            ->setLieu('Senegal')
            ->SetIsValid(false)
            ->setTelephone(2424);
        $manager->persist($emedia);

        $manager->flush();
    }
}
