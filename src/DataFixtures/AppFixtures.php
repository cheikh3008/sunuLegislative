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

        $manager->flush();
    }
}
