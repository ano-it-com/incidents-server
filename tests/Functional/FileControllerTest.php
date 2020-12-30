<?php


namespace App\Tests\Functional;

use App\Entity\File\File;
use App\Fixtures\FileFixture;
use App\Fixtures\SecurityFixtures;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;


class FileControllerTest extends BaseWebTestCase
{
    public function testUpload()
    {
        $client = static::createClient();
        $this->loadFixtures([SecurityFixtures::class]);
        $client->amBearerAuthenticatedByLogin('supervisor');

        $photo = new UploadedFile(self::getTestFilePath('photo.jpg'), 'photo.jpg', 'image/jpeg');

        $client->request('POST', 'file/upload', [], ['file' => $photo]);
        $response = $client->getJsonResponseAsArray();

        $this->assertArrayHasKey('id', $response);
        $this->assertIsInt($response['id']);
    }

    public function testDownload()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([SecurityFixtures::class, FileFixture::class]);
        $client->amBearerAuthenticatedByLogin('supervisor');

        /** @var File $file */
        $file = $fixtures->getReferenceRepository()->getReference(FileFixture::getReferenceFile());

        $client->request('GET', "/file/{$file->getId()}/download");

        /** @var BinaryFileResponse $response */
        $response = $client->getResponse();
        $this->assertInstanceOf(BinaryFileResponse::class, $response);
        $this->assertNotEmpty($file = $response->getFile());
        $this->assertEquals(FileFixture::getFileContent(), file_get_contents($file->getRealPath()));
    }
}