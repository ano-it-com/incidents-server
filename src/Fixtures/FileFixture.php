<?php


namespace App\Fixtures;


use App\Entity\File\File;
use App\Entity\Security\User;
use App\Services\FileService;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FileFixture extends AbstractFixture implements DependentFixtureInterface
{
    /** @var FileService */
    protected $fileService;

    public function __construct(FileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public static function getFileContent(){
        return 'testData';
    }

    public static function getReferenceDeletedUserFile($userLogin)
    {
        return "deletedFile::user::{$userLogin}";
    }

    public static function getReferenceUserFile($userLogin)
    {
        return "file::user::{$userLogin}";
    }

    public static function getReferenceDeletedFile()
    {
        return "deletedFile";
    }

    public static function getReferenceFile()
    {
        return "file";
    }

    public function load(ObjectManager $manager)
    {
        /** @var User $user */
        $users[] = $this->getReference('user::supervisor');
        $users[] = $this->getReference('user::admin');
        $users[] = $this->getReference('user::executor');

        foreach ($users as $user) {
            $deletedFile = new File();
            $deletedFile->setPath('deletedFile.txt');
            $deletedFile->setOriginalName('deletedFile.txt');
            $deletedFile->setSize(strlen(self::getFileContent()));
            $deletedFile->setCreatedBy($user);
            $deletedFile->setDeleted(true);
            $deletedFile->setOwnerCode('user');
            $deletedFile->setOwnerId($user->getId());

            $file = clone $deletedFile;
            $file->setPath('file.txt');
            $file->setOriginalName('file.txt');
            $file->setDeleted(false);

            $manager->persist($deletedFile);
            $manager->persist($file);
            $manager->flush();

            $this->setReference(self::getReferenceDeletedUserFile($user->getLogin()), $deletedFile);
            $this->setReference(self::getReferenceDeletedFile(), $deletedFile);

            $this->setReference(self::getReferenceUserFile($user->getLogin()), $file);
            $this->setReference(self::getReferenceFile(), $file);

            file_put_contents($this->fileService->getFilePath($deletedFile), self::getFileContent());
            file_put_contents($this->fileService->getFilePath($file), self::getFileContent());
        }
    }


    public function getDependencies()
    {
        return [SecurityFixtures::class];
    }
}