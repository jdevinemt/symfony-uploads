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

class ArticleAdminController extends AbstractController
{
    // TODO add security
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
                $imageFilename = $uploaderHelper->uploadArticleImage($uploadedFile);
                $article->setImageFilename($imageFilename);
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

    // TODO add security
    #[Route('/article/{id}/edit', name: 'app_article_edit')]
    public function edit(Article $article, Request $request, EntityManagerInterface $em, UploaderHelper $uploaderHelper): Response
    {
        $form = $this->createForm(ArticleFormType::class, $article);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $form->get('imageFile')->getData();

            if($uploadedFile){
                $imageFilename = $uploaderHelper->uploadArticleImage($uploadedFile, $article->getImageFilename());
                $article->setImageFilename($imageFilename);
            }

            $em->persist($article);
            $em->flush();

            return $this->redirectToRoute('app_article_edit', [
                'id' => $article->getId()
            ]);
        }

        return $this->render('article/edit.html.twig', [
            'articleForm' => $form->createView(),
            'article' => $article
        ]);
    }
}
