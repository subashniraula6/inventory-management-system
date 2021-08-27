<?php

namespace App\DataFixtures;

use App\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
    public function loadRole(ObjectManager $manager)
    {
        $role1 = new Role();
        $role1->setName("Employee");
        $role1->setCreatedAt(\DateTimeImmutable::createFromFormat('2012-02-02'));

        $manager->flush();
    }
    public function loadUser(ObjectManager $manager)
    {
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
