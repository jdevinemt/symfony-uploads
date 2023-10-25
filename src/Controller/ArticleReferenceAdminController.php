<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\ArticleReference;
use App\Service\UploaderHelper;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
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
    ): JsonResponse
    {
        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $request->files->get('reference');

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
            return $this->json($violation, 400);
        }

        $filename = $uploaderHelper->uploadArticleReference($uploadedFile);

        $articleReference = new ArticleReference($article);
        $articleReference->setFilename($filename);
        $articleReference->setOriginalFilename($uploadedFile->getClientOriginalName() ?? $filename);
        $articleReference->setMimeType($uploadedFile->getMimeType() ?? 'application/octet-stream');

        $em->persist($articleReference);
        $em->flush();

        return $this->json($articleReference, 201, [], ['groups' => 'main']);
    }

    // TODO add security
    #[Route('/admin/article/references/{id}/download', name: 'admin_article_download_reference', methods: ['GET'])]
    public function downloadArticleReference(ArticleReference $reference, S3Client $s3Client, string $s3BucketName): Response
    {
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $reference->getOriginalFilename()
        );

        $cmd = $s3Client->getCommand('GetObject', [
            'Bucket' => $s3BucketName,
            'Key' => $reference->getFilePath(),
            'ResponseContentType' => $reference->getMimeType(),
            'ResponseContentDisposition' => $disposition
        ]);

        $request = $s3Client->createPresignedRequest($cmd, '+30 minutes');

        return new RedirectResponse((string)$request->getUri(), 302, [
            'Content-Disposition'
        ]);
    }

    // TODO add security
    #[Route('/admin/article/{id}/references', name: 'admin_article_references', methods: ['GET'])]
    public function getArticleReferences(Article $article): JsonResponse
    {
        return $this->json($article->getArticleReferences(), 200, [], ['groups' => 'main']);
    }

    // TODO add security
    #[Route('/admin/article/references/{id}', name: 'admin_article_reference_delete', methods: ['DELETE'])]
    public function deleteArticleReference(
        ArticleReference $articleReference,
        UploaderHelper $uploaderHelper,
        EntityManagerInterface $em
    ): Response
    {
        $em->remove($articleReference);
        $em->flush();

        $uploaderHelper->deleteFile($articleReference->getFilePath());

        return new Response(null, 204);
    }

    #[Route('/admin/article/references/{id}', name: 'admin_article_reference_update', methods: ['PUT'])]
    public function updateArticleReference(
        ArticleReference $articleReference,
        EntityManagerInterface $em,
        SerializerInterface $serializer,
        Request $request,
        ValidatorInterface $validator
    ): JsonResponse
    {
        $serializer->deserialize($request->getContent(), ArticleReference::class, 'json', [
            'object_to_populate' => $articleReference,
            'groups' => 'input'
        ]);

        $violations = $validator->validate($articleReference);

        if($violations->count() > 0){
            return $this->json($violations, 400);
        }

        $em->persist($articleReference);
        $em->flush();

        return $this->json($articleReference, 200, [], ['groups' => 'main']);
    }
}