<?php

namespace App\Services;

use App\Entity\File\File;
use App\Entity\File\FileOwnerInterface;
use App\Entity\Security\User;
use App\Repository\File\FileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class FileService
{
    /** @var string */
    private $filesPath;

    /** @var EntityManagerInterface */
    private $em;

    /** @var FileRepository */
    private $fileRepository;

    /** @var UrlGeneratorInterface */
    private $generator;


    public function __construct(KernelInterface $kernel, EntityManagerInterface $em, FileRepository $fileRepository, UrlGeneratorInterface $generator)
    {
        $this->filesPath = $kernel->getProjectDir() . '/var/storage/uploads/files';
        if(!file_exists($this->filesPath)){
            mkdir($this->filesPath, 0777, true);
        }
        $this->em = $em;
        $this->fileRepository = $fileRepository;
        $this->generator = $generator;
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param User|UserInterface $user
     * @return File
     */
    public function storeDraft(UploadedFile $uploadedFile, User $user): File
    {
        $originalFilename = $uploadedFile->getClientOriginalName();
        $size = $uploadedFile->getSize();

        $safeFilename = uniqid('ims_', true);

        $newFilename = $safeFilename . '.' . $uploadedFile->getClientOriginalExtension();

        $uploadedFile->move(
            $this->filesPath,
            $newFilename
        );

        $file = new File();
        $file->setOwnerCode('draft');
        $file->setOwnerId(0);
        $file->setPath($newFilename);
        $file->setOriginalName($originalFilename);
        $file->setSize($size);
        $file->setDeleted(true);
        $file->setCreatedAt(new \DateTimeImmutable());
        $file->setCreatedBy($user);

        $this->em->persist($file);

        $this->em->flush();

        return $file;

    }

    public function getFileContent(File $file): string
    {
        return file_get_contents($this->filesPath . DIRECTORY_SEPARATOR . $file->getPath());
    }

    public function getFileExtension(File $file): string
    {
        $ext = pathinfo($this->getFilePath($file), PATHINFO_EXTENSION);
        return strtolower($ext);
    }

    public function getFilePath(File $file): string
    {
        return $this->filesPath . DIRECTORY_SEPARATOR . $file->getPath();
    }

    public function attachFilesTo(FileOwnerInterface $owner, array $fileIds, ?string $ownerCode = null): void
    {
        $files = $this->fileRepository->findBy(['id' => $fileIds, 'deleted' => true]);

        if (!$ownerCode) {
            $ownerCode = $owner->getOwnerCode();
        }

        $ownerId = $owner->getId();

        foreach ($files as $file) {
            $file->setOwnerCode($ownerCode);
            $file->setOwnerId($ownerId);
            $file->setDeleted(false);
        }

        $this->em->flush();
    }

    public function attachFilesWithCopyTo(FileOwnerInterface $owner, array $fileIds, ?string $ownerCode = null): void
    {
        $files = $this->fileRepository->findBy(['id' => $fileIds, 'deleted' => true]);

        if (!$ownerCode) {
            $ownerCode = $owner->getOwnerCode();
        }

        $ownerId = $owner->getId();

        foreach ($files as $file) {
            $newFile = new File();

            $newFile->setOwnerCode($ownerCode);
            $newFile->setOwnerId($ownerId);
            $newFile->setPath($file->getPath());
            $newFile->setOriginalName($file->getOriginalName());
            $newFile->setSize($file->getSize());
            $newFile->setDeleted(false);
            $newFile->setCreatedAt($file->getCreatedAt());
            $newFile->setCreatedBy($file->getCreatedBy());

            $this->em->persist($newFile);
        }

        $this->em->flush();
    }
}