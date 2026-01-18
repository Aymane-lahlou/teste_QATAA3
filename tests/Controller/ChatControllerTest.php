<?php

namespace App\Tests;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ChatControllerTest extends WebTestCase
{
    public function testChatGreeting(): void
    {
        $client = static::createClient();
        
        // Test salutation en français
        $client->jsonRequest('POST', '/chat-api', ['message' => 'Bonjour']);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('reply', $data);
        $this->assertStringContainsString('Bonjour', $data['reply']);
    }

    public function testChatHelp(): void
    {
        $client = static::createClient();
        
        // Test demande d'aide
        $client->jsonRequest('POST', '/chat-api', ['message' => 'Aide']);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('reply', $data);
        $this->assertArrayHasKey('suggestions', $data);
        $this->assertTrue($data['suggestions']);
    }

    public function testChatTicketsRequest(): void
    {
        $client = static::createClient();
        
        // Test demande de tickets
        $client->jsonRequest('POST', '/chat-api', ['message' => 'Combien de tickets restent?']);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('reply', $data);
        $this->assertStringContainsString('Tickets', $data['reply']);
    }

    public function testChatEventSearch(): void
    {
        $client = static::createClient();
        
        // Test recherche d'événements
        $client->jsonRequest('POST', '/chat-api', ['message' => 'concerts']);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('reply', $data);
    }

    public function testChatLanguageDetection(): void
    {
        $client = static::createClient();
        
        // Test détection d'arabe
        $client->jsonRequest('POST', '/chat-api', ['message' => 'السلام عليكم']);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertEquals('ar', $data['language'] ?? null);
    }

    public function testChatEmptyMessage(): void
    {
        $client = static::createClient();
        
        // Test message vide
        $client->jsonRequest('POST', '/chat-api', ['message' => '']);
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true);
        
        $this->assertArrayHasKey('reply', $data);
        $this->assertStringContainsString('cherches', $data['reply']);
    }
}
