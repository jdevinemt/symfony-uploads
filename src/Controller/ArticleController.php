<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleFormType;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ArticleController extends AbstractController
{
    #[Route('/article/new', name: 'app_article_new')]
    public function new(Request $request, EntityManagerInterface $em, UploaderHelper $uploaderHelper): Response
    {
        $form = $this->createForm(ArticleFormType::class);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            /** @var Article $article */
            $article = $form->getData();

            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $form->get('imageFile')->getData();

            if($uploadedFile){
                $imageFile = $uploaderHelper->uploadArticleImage($uploadedFile);
                $article->setImageFilename($imageFile->getBasename());
            }

            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('app_article_edit', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('article/new.html.twig', [
            'articleForm' => $form->createView()
        ]);
    }

    #[Route('/article/{id}/edit', name: 'app_article_edit')]
    public function edit(Article $article, Request $request, EntityManagerInterface $em, UploaderHelper $uploaderHelper): Response
    {
        $form = $this->createForm(ArticleFormType::class, $article);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $form->get('imageFile')->getData();

            if($uploadedFile){
                $imageFile = $uploaderHelper->uploadArticleImage($uploadedFile);
                $article->setImageFilename($imageFile->getBasename());
            }

            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('app_article_edit', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('article/edit.html.twig', [
            'articleForm' => $form->createView()
        ]);
    }

    // todo this would use a slug instead of the id in a real application
    #[Route('/article/{id}')]
    public function show(Article $article): Response
    {
        return $this->render('article/show.html.twig', [
            'article' => $article
        ]);
    }
}
