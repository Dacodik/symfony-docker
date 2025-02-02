<?php

namespace App\Controller;

use App\Entity\Comment;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Repository\PostRepository;
use App\Repository\UserRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
class CommentController extends AbstractController
{
    public function __construct(
        private readonly PostRepository $postRepository,
        private readonly UserRepository $userRepository
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $users = $this->userRepository->findAll();
        $posts = $this->postRepository->findAll();

        foreach ($posts as $post) {
            for ($i = 0; $i < mt_rand(0, 15); $i++) {
                $comment = new Comment();
                $comment->setContent($faker->realText())
                    ->setIsApproved(mt_rand(0, 3) === 0 ? false : true)
                    ->setAuthor($faker->randomElement($users))
                    ->setCreatedAt($faker->dateTimeBetween('-6 months'))
                    ->setPost($post);

                $manager->persist($comment);
                $post -> addComment($comment);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            PostFixtures::class,
        ];
    }
}
