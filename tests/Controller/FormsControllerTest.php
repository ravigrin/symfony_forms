<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\ContactMessage;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class FormsControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = static::createClient();
        $this->entityManager = static::getContainer()->get('doctrine')->getManager();
    }

    public function testIndexPageLoads(): void
    {
        $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Формы регистрации и обратной связи');
    }

    public function testRegistrationPageHasRequiredElements(): void
    {
        $this->client->request('GET', '/');

        $this->assertSelectorExists('#registrationForm');
        $this->assertSelectorExists('#registration_name');
        $this->assertSelectorExists('#registration_email');
        $this->assertSelectorExists('#registration_phone');
        $this->assertSelectorExists('#registration_password');
        $this->assertSelectorExists('#registration_confirm_password');
    }

    public function testContactPageHasRequiredElements(): void
    {
        $this->client->request('GET', '/');

        $this->assertSelectorExists('#contactForm');
        $this->assertSelectorExists('#contact_email');
        $this->assertSelectorExists('#contact_message');
    }

    public function testRegistrationSuccess(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+79001234567',
            'password' => 'password123',
            'confirmPassword' => 'password123',
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Test User', $responseData['user']['name']);
        $this->assertEquals('test@example.com', $responseData['user']['email']);
        $this->assertEquals('+79001234567', $responseData['user']['phone']);

        // Проверяем, что пользователь сохранен в БД
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@example.com']);
        $this->assertNotNull($user);
        $this->assertEquals('Test User', $user->getName());
        // Пароль в базе должен храниться в виде хэша, а не в открытом виде
        $this->assertNotEquals('password123', $user->getPassword());
        $this->assertTrue(password_verify('password123', $user->getPassword()));
    }

    public function testRegistrationValidationError(): void
    {
        $data = [
            'name' => 'T', // Слишком короткое имя
            'email' => 'invalid-email', // Невалидный email
            'phone' => '123', // Слишком короткий телефон
            'password' => '123', // Слишком короткий пароль
            'confirmPassword' => '456', // Пароли не совпадают
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(400);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertArrayHasKey('errors', $responseData);
    }

    public function testRegistrationPasswordMismatch(): void
    {
        $data = [
            'name' => 'Test User',
            'email' => 'test2@example.com',
            'phone' => '+79001234568',
            'password' => 'password123',
            'confirmPassword' => 'different_password',
        ];

        $this->client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(400);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertArrayHasKey('confirmPassword', $responseData['errors']);
    }

    public function testContactMessageSuccess(): void
    {
        // Сначала создаем пользователя
        $user = new User();
        $user->setName('Existing User');
        $user->setEmail('existing@example.com');
        $user->setPhone('+79001234569');
        $user->setPassword('password123');
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        $data = [
            'email' => 'existing@example.com',
            'message' => 'This is a test message',
        ];

        $this->client->request(
            'POST',
            '/api/contact',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('Existing User', $responseData['message']['displayName']); // Имя вместо email
        $this->assertEquals('This is a test message', $responseData['message']['message']);

        // Проверяем, что сообщение сохранено в БД
        $message = $this->entityManager->getRepository(ContactMessage::class)->findOneBy(['email' => 'existing@example.com']);
        $this->assertNotNull($message);
    }

    public function testContactMessageWithUnknownEmail(): void
    {
        $data = [
            'email' => 'unknown@example.com',
            'message' => 'This is a test message',
        ];

        $this->client->request(
            'POST',
            '/api/contact',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseIsSuccessful();
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertTrue($responseData['success']);
        $this->assertEquals('unknown@example.com', $responseData['message']['displayName']); // Email вместо имени
    }

    public function testContactMessageValidationError(): void
    {
        $data = [
            'email' => 'invalid-email',
            'message' => 'short',
        ];

        $this->client->request(
            'POST',
            '/api/contact',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(400);
        
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertFalse($responseData['success']);
        $this->assertArrayHasKey('errors', $responseData);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Очистка базы данных после каждого теста
        if ($this->entityManager) {
            $this->entityManager->close();
        }
    }
}