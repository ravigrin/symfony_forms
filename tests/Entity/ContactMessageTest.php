<?php

namespace App\Tests\Entity;

use App\Entity\ContactMessage;
use PHPUnit\Framework\TestCase;

class ContactMessageTest extends TestCase
{
    public function testContactMessageCreation(): void
    {
        $message = new ContactMessage();
        $message->setEmail('test@example.com');
        $message->setMessage('Test message content');

        $this->assertEquals('test@example.com', $message->getEmail());
        $this->assertEquals('Test message content', $message->getMessage());
        $this->assertNull($message->getId());
        $this->assertInstanceOf(\DateTimeImmutable::class, $message->getCreatedAt());
    }

    public function testContactMessageSettersReturnSelf(): void
    {
        $message = new ContactMessage();
        
        $result = $message->setEmail('test@example.com');
        $this->assertSame($message, $result);
        
        $result = $message->setMessage('Test message');
        $this->assertSame($message, $result);
    }
}