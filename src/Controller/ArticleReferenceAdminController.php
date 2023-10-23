<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleReference;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ArticleReferenceAdminController extends AbstractController
{
    // TODO add security
    #[Route('/admin/article/{id}/references', name: 'admin_article_add_reference', methods: ['POST'])]
    public function uploadArticleReference(
        Article $article,
        Request $request,
        UploaderHelper $uploaderHelper,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ): Response
    {
        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $request->files->get('reference');
dump($uploadedFile);
        $violations = $validator->validate(
            $uploadedFile,
            [
                new File([
                    'maxSize' => '5m',
                    'mimeTypes' => [
                        'image/*',
                        'application/pdf',
                        'text/plain',
                        'application/msword',
                        'application/vnd.ms-excel'
                    ]
                ]),
                new NotBlank(['message' => 'Please select a file to upload.'])
            ]
        );

        if($violations->count() > 0){
            /** @var ConstraintViolation $violation */
            $violation = $violations[0];
            return new Response('Error: '.$violation->getMessage(), 400);
        }

        $filename = $uploaderHelper->uploadArticleReference($uploadedFile);

        $articleReference = new ArticleReference($article);
        $articleReference->setFilename($filename);
        $articleReference->setOriginalFilename($uploadedFile->getClientOriginalName() ?? $filename);
        $articleReference->setMimeType($uploadedFile->getMimeType() ?? 'application/octet-stream');

        $em->persist($articleReference);
        $em->flush();

        return $this->redirectToRoute('app_article_edit', ['id' => $article->getId()]);
    }

    // TODO add security
    #[Route('/admin/article/references/{id}/download', name: 'admin_article_download_reference', methods: ['GET'])]
    public function downloadArticleReference(ArticleReference $reference, UploaderHelper $uploaderHelper): Response
    {
        $response = new StreamedResponse(function() use ($reference, $uploaderHelper){
            $outputStream = fopen('php://output', 'wb');
            $fileStream = $uploaderHelper->readStream($reference->getFilePath(), false);

            stream_copy_to_stream($fileStream, $outputStream);
        });

        $response->headers->set('Content-Type', $reference->getMimeType());

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $reference->getOriginalFilename()
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}