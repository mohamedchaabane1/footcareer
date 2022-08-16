<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher = $hasher;
    }

    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        $admin = $this->createAdmin();

        $manager->persist($admin);
       
        
        $manager->flush();
    }

    private function createAdmin()
    {
        $admin = new User();

        $passwordHashed = $this->hasher->hashPassword($admin,"azerty12345A&");
        
        $admin->setLastName('Chaabane');
        $admin->setFirstName('Souleiman');
        $admin->setEmail('blog-foot@gmail.com');
        $admin->setPassword($passwordHashed);
        $admin->setRoles(array('ROLE_ADMIN'));
        $admin->setIsVerified(true);
        $admin->setVerifiedAt(new \DateTimeImmutable('now'));

        return $admin;

    }

    
}
