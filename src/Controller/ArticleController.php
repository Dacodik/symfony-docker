<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Article;
use App\Form\ArticleType;
use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;

class ArticleController extends AbstractController
{
    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        return $this->render('article/index.html.twig', [
            'controller_name' => 'ArticleController',
        ]);
    }

    #[Route('/article/generate', name: 'generate_article')]
    public function generateArticle(Request $request, EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $form = $this->createForm(ArticleType::class, $article);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($article);
            $entityManager->flush();

            return $this->redirectToRoute('show_article', ['id' => $article->getId()]);
        }

        return $this->render('article/new.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
        ]);
    }

    #[Route('/article/list', name: 'list_article')]
    public function listArticle(EntityManagerInterface $entityManager): Response
    {
        $articleRepository = $entityManager->getRepository(Article::class);
        $articles = $articleRepository->findAll();
    
        return $this->render('article/list.html.twig', [
            'articles' => $articles,
            'controller_name' => 'ListController',

        ]);
    }

    #[Route('/article/{id}', name: 'show_article', requirements: ['id' => '\d+']/*permet d'éviter une erreur qui faisait que je ne pouvais pas définir mes fonctions dans l'ordre voulue. Symfony va maintenant chercher si l'ID est un entier et donc distinguer les routes dynamiques des statiques. Ca me permet de pouvoir définir mon article/new où je veux dans le code.*/
    )]
    public function showArticle($id, EntityManagerInterface $entityManager): Response
    {
        $articleRepository = $entityManager->getRepository(Article::class);
        $articles = $articleRepository->find($id);
        $comments = $entityManager->getRepository(Comment::class)->findBy(['post' => $articles]);
        $this ->addFlash('success', 'Article chargé!');

        return $this->render('article/show.html.twig', [
            'articles' => $articles,
            'comments' => $comments,
            'controller_name' => 'ShowController',

        ]);
    }

    #[Route('/article/new', name: 'article_new')]
    public function new(EntityManagerInterface $entityManager): Response
    {
        $article = new Article();
        $article->setTitre("Which Title ?");
        $article->setTexte("And which content ?");
        $now = time();
        $str_now = date('Y-m-d H:i:s', $now);
        $article->setDate(\DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $str_now));
    
    $form = $this->createForm(ArticleType::class, $article);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this ->addFlash('success', 'Article créé!');

        return $this->redirectToRoute('list_article');
    }

    return $this->render('article/new.html.twig', [
        'form' => $form->createView(),
        'article' => $article,
    ]);
    }

    #[Route('/article/edit/{id}', name: 'article_edit', requirements: ['id' => '\d+']/*nécessaire à chaque fois que l'id est utilisé dans la barre de navigation*/)]
    public function edit($id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $articleRepository = $entityManager->getRepository(Article::class);
        $articles = $articleRepository->find($id);

        $form = $this->createForm(ArticleType::class, $articles);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this ->addFlash('success', 'Article modifié!');

            return $this->redirectToRoute('show_article', ['id' => $articles->getId()]);
        }

        return $this->render('article/edit.html.twig', [
            'form' => $form->createView(),
            'article' => $articles,
        ]);
    }
    
    #[Route('/article/delete/{id}', name: 'article_delete', requirements: ['id' => '\d+'])]
    public function delete($id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $articleRepository = $entityManager->getRepository(Article::class);
        $articles = $articleRepository->find($id);

        $entityManager->remove($articles);
        $entityManager->flush();

        return $this->redirectToRoute('list_article');
    }
}
