<?php

namespace App\Controller\Api;

use App\Infrastructure\Exceptions\ValidationException;
use App\Infrastructure\Response\ResponseFactory;
use App\Repository\File\FileRepository;
use App\Services\FileService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use OpenApi\Annotations as OA;

class FileController extends AbstractController
{
    /** @var Security */
    private $security;

    /** @var FileService */
    private $fileService;

    public function __construct(FileService $fileService, Security $security)
    {
        $this->security = $security;
        $this->fileService = $fileService;
    }

    /**
     * Загрузка файла
     *
     * @Route("/file/upload", name="ims_file_upload", methods={"POST"})
     * @OA\RequestBody(
     *      required=true,
     *      @OA\MediaType(
     *          mediaType="multipart/form-data",
     *          @OA\Schema(
     *              @OA\Property(
     *                  description="file to upload",
     *                  property="file",
     *                  type="string",
     *                  format="file",
     *              ),
     *              required={"file"}
     *          )
     *      )
     *  )
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        /** @var UploadedFile $file */
        $uploadedFile = $request->files->get('file');

        if (!$uploadedFile) {
            throw new ValidationException(['file' => 'File not found']);
        }
        if (!$uploadedFile->isValid()) {
            throw new ValidationException(['file' => 'File not valid']);
        }

        $file = $this->fileService->storeDraft($uploadedFile, $this->security->getUser());

        return ResponseFactory::success(['id' => $file->getId()]);
    }


    /**
     * Скачивание файла
     *
     * @Route("/file/{fileId}/download", name="ims_file_download", methods={"GET"})
     * @param int $fileId
     * @param FileRepository $fileRepository
     *
     * @return BinaryFileResponse
     */
    public function download(int $fileId, FileRepository $fileRepository)
    {
        $file = $fileRepository->find($fileId);
        if (!$file) {
            throw new NotFoundHttpException('File not found');
        }

        $response = new BinaryFileResponse($this->fileService->getFilePath($file));
        $ext = $this->fileService->getFileExtension($file);
        if (in_array($ext, ['jpg', 'jpeg', 'gif', 'tiff', 'bmp', 'png'], true)) {
            $dispositionType = ResponseHeaderBag::DISPOSITION_INLINE;
            $response->headers->set('Content-Type', 'image/' . $ext);
        } elseif ($ext === 'pdf') {
            $dispositionType = ResponseHeaderBag::DISPOSITION_INLINE;
            $response->headers->set('Content-Type', 'application/pdf');
        } else {
            $dispositionType = ResponseHeaderBag::DISPOSITION_ATTACHMENT;
        }

        $originalFileName = str_replace('"', '', $file->getOriginalName());
        $response->headers->set('Content-Disposition', $dispositionType . '; filename="' . $originalFileName . '"');

        return $response;
    }

}