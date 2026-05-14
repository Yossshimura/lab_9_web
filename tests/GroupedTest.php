<?php

use PHPUnit\Framework\TestCase;

/**
 * @group fast
 */
class FastTest extends TestCase
{
    public function testFastOperation(): void
    {
        $this->assertEquals(2, 1 + 1);
    }
}

/**
 * @group slow
 */
class SlowTest extends TestCase
{
    public function testSlowOperation(): void
    {
        sleep(1);
        $this->assertTrue(true);
    }
}

/**
 * @group database
 */
class DatabaseTest extends TestCase
{
    public function testDatabaseConnection(): void
    {
        $host = $_ENV['DB_HOST'] ?? 'db';
        $db = $_ENV['DB_NAME'] ?? 'test_db';
        $user = $_ENV['DB_USER'] ?? 'test_user';
        $pass = $_ENV['DB_PASSWORD'] ?? 'test_pass';
        
        try {
            $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
            $this->assertNotNull($pdo);
        } catch (PDOException $e) {
            $this->markTestSkipped('Database not available: ' . $e->getMessage());
        }
    }
}