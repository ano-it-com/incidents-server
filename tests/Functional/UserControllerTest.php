<?php


namespace App\Tests\Functional;


use App\Entity\Security\User;
use App\Fixtures\SecurityFixtures;

class UserControllerTest extends BaseWebTestCase
{
    public function testGetUser()
    {
        $client = static::createClient();
        $fixtures = $this->loadFixtures([SecurityFixtures::class]);

        /** @var User $user */
        $user = $fixtures->getReferenceRepository()->getReference('user::supervisor');
        $client->amBearerAuthenticatedByLogin($user->getLogin());

        $client->request('GET', '/user');

        $this->assertEquals(200, $client->getResponse()->getStatusCode());

        $response = $client->getJsonResponseAsArray();
        foreach (['login', 'email', 'first_name', 'last_name', 'rights'] as $key) {
            $this->assertArrayHasKey($key, $response);
            $this->assertNotEmpty($response[$key]);
        }
    }
}