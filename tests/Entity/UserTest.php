<?php

namespace App\Tests\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    public function testUserCreation(): void
    {
        $user = new User();
        $user->setName('Test User');
        $user->setEmail('test@example.com');
        $user->setPhone('+79001234567');
        $user->setPassword('password123'); // в тестах работаем с «сырой» строкой, контроллер хэширует перед сохранением

        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('+79001234567', $user->getPhone());
        $this->assertEquals('password123', $user->getPassword());
        $this->assertNull($user->getId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $user->getCreatedAt());
    }

    public function testUserSettersReturnSelf(): void
    {
        $user = new User();
        
        $result = $user->setName('Test User');
        $this->assertSame($user, $result);
        
        $result = $user->setEmail('test@example.com');
        $this->assertSame($user, $result);
        
        $result = $user->setPhone('+79001234567');
        $this->assertSame($user, $result);
        
        $result = $user->setPassword('password123');
        $this->assertSame($user, $result);
    }
}