<?php


namespace App\Tests\Functional;


use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class UserResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateUser()
    {
        $client = self::createClient();

        $client->request('POST', '/api/users', [
            'json' => [
                'email' => 'human@example.com',
                'username' => 'human',
                'password' => '1234'
            ]
        ]);
        $this->assertResponseStatusCodeSame(201);

        $this->logIn($client, 'human@example.com', '1234');
    }

    public function testUpdateUser()
    {
        $client = self::createClient();
        $user = $this->createUserAndLogIn($client, 'beast@example.com', 'beast');

        $client->request('PUT', '/api/users/'.$user->getId(), [
            'json' => ['username' => 'newusername']
        ]);
        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            'username' => 'newusername'
        ]);
    }
}