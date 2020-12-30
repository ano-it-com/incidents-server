<?php


namespace App\Tests\Functional;

use App\Fixtures\SecurityFixtures;
use App\Entity\Security\User;


class SecurityControllerTest extends BaseWebTestCase
{
    public function testLogin()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([SecurityFixtures::class]);
        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::supervisor');

        $client->jsonRequest('POST', '/login', [
                'login' => $user->getLogin(),
                'password' => SecurityFixtures::getPassword()]
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getJsonResponseAsArray();
        $this->assertArrayHasKey('token', $response);
        $this->assertNotEmpty($response['token']);
    }

    public function testAuthSupports()
    {
        $client = static::createClient();
        $client->jsonRequest('GET', '/auth/supports');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $response = $client->getJsonResponseAsArray();

        foreach ($response as $item){
            $this->assertArrayHasKey('type', $item);
            $this->assertArrayHasKey('link', $item);
        }
    }

    public function testLogout(){
        $client = static::createClient();
        $fixtures = $this->loadFixtures([SecurityFixtures::class]);
        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::supervisor');
        $client->amBearerAuthenticatedByLogin($user->getLogin());

        $client->jsonRequest('GET', '/logout');
        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        //Проверка инвалидации токена
        $client->request('GET', '/logout');
        $this->assertEquals(401, $client->getResponse()->getStatusCode());
    }

    public function testDenyAccess()
    {
        $client = static::createClient();
        $this->loadFixtures([SecurityFixtures::class]);

        $client->request('GET', '/logout');

        $this->assertEquals(403, $client->getResponse()->getStatusCode());
    }
}