<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setUsername('admin');
        $user->setRoles(['ROLE_USER', 'ROLE_API', 'ROLE_ADMIN']);
        $user->setToken(bin2hex(random_bytes(32)));
        $user->setPassword(
            $this->userPasswordHasher->hashPassword($user, 'test123')
        );
        $manager->persist($user);
        $manager->flush();
    }
}
