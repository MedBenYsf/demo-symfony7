<?php

namespace App\DataFixtures;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create 2 users
        $user1 = new User();
        $user1->setEmail('user1@api-books.com')
              ->setRoles(['ROLE_USER'])
              ->setPassword($this->userPasswordHasher->hashPassword($user1, 'password'));
        $manager->persist($user1);

        $user2 = new User();
        $user2->setEmail('user2@api-books.com')
            ->setRoles(['ROLE_USER'])
            ->setPassword($this->userPasswordHasher->hashPassword($user2, 'password'));
        $manager->persist($user2);

        // Create authors
        $authors = [];
        for ($i=0; $i<5; $i++) {
            $author = new Author();
            $author->setFirstname('firstname author'.$i)
                   ->setLastname('lastname author'.$i);
            $manager->persist($author);
            $authors[]= $author;
        }
        for ($i=0; $i<20; $i++) {
            $book = new Book();
            $book->setTitle('book'.$i)
                 ->setCoverText('cover text of book'.$i)
                 ->setAuthor($authors[array_rand($authors)]);

            $manager->persist($book);
        }

        $manager->flush();
    }
}
