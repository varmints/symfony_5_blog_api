<?php


namespace App\Tests\Functional;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use PHPUnit\Framework\TestCase;

class ArticleResourceTest extends ApiTestCase
{
    public function testCreateArticle()
    {
        $client = self::createClient();

        $client->request('POST', '/api/articles', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => []
        ]);
        $this->assertResponseStatusCodeSame(401);
    }
}