<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AdminFixture extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // FIRST ADMIN
        $admin = new User();
        $admin->setUsername('admin');
        $admin->setRoles(['ROLE_ADMIN']);

        $hashedPassword = $this->passwordHasher->hashPassword($admin, 'troy');
        $admin->setPassword($hashedPassword);

        $manager->persist($admin);

        // SECOND ADMIN
        $anotheradmin = new User();
        $anotheradmin->setUsername('anotheradmin');
        $anotheradmin->setRoles(['ROLE_ADMIN']);

        $hashedPassword2 = $this->passwordHasher->hashPassword($anotheradmin, 'grefalde');
        $anotheradmin->setPassword($hashedPassword2);

        $manager->persist($anotheradmin);

        // SAVE BOTH
        $manager->flush();
    }
}
