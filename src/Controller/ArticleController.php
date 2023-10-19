<?php

namespace App\Controller;

use App\Entity\Article;
use App\Form\ArticleFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

class ArticleController extends AbstractController
{
    #[Route('/article/{id}/edit', name: 'app_article_edit')]
    public function edit(Article $article, Request $request, EntityManagerInterface $em, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ArticleFormType::class, $article);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            /** @var UploadedFile|null $uploadedFile */
            $uploadedFile = $form->get('imageFile')->getData();

            if($uploadedFile){
                $destination = $this->getParameter('kernel.project_dir').'/public/uploads/article_image';

                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = $slugger->slug($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();

                $file = $uploadedFile->move($destination, $newFilename);

                $article->setImageFilename($file->getBasename());
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
}
