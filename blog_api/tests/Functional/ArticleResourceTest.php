<?php declare(ticks=1);


namespace App\Tests\Functional;

use App\Entity\Article;
use App\Entity\User;
use App\Test\CustomApiTestCase;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class ArticleResourceTest extends CustomApiTestCase
{
    use ReloadDatabaseTrait;

    public function testCreateArticle()
    {
        $client = self::createClient();

        $client->request('POST', '/api/articles', [
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(401);


        $this->createUserAndLogIn($client, 'example1@example.com', 'pass');

        $client->request('POST', '/api/articles', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(422);
    }

    public function testUpdateArticle()
    {
        $client = self::createClient();
        $user1 = $this->createUser('example1@example.com', 'pass');
        $user2 = $this->createUser('example2@example.com', 'foo');

        $article = new Article('HAllo');
        $article->setOwner($user1);
        $article->setIsPublished(false);
        $article->setLongContent("lalala");

        $em = $this->getEntityManager();
        $em->persist($article);
        $em->flush();

        $this->logIn($client, 'example2@example.com', 'foo');
        $client->request('PUT', '/api/articles/'.$article->getId(), [
            'json' => ['title' => 'updates']
        ]);
        $this->assertResponseStatusCodeSame(403);

        $this->logIn($client, 'example1@example.com', 'pass');
        $client->request('PUT', '/api/articles/'.$article->getId(), [
            'json' => ['title' => 'updates']
        ]);

        $this->assertResponseStatusCodeSame(200);
    }
}