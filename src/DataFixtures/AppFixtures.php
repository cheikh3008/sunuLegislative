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

        $user = new User();
        $user->setUsername(773043248)
            ->setPassword($this->encoder->hashPassword($user, "admin123"))
            ->setNom('Dieng')
            ->setPrenom('Cheikh')
            ->setUuid('Greush221')
            ->setRole($role_admin)
            ->setTelephone(773043248);
        $manager->persist($user);

        $manager->flush();
    }
}
