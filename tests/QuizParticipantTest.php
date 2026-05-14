<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../www/QuizParticipant.php';

class QuizParticipantTest extends TestCase
{
    private $pdoMock;
    private $stmtMock;
    private $quizParticipant;

    protected function setUp(): void
    {
        $this->pdoMock = $this->createMock(PDO::class);
        $this->stmtMock = $this->createMock(PDOStatement::class);
        
        $this->quizParticipant = new QuizParticipant($this->pdoMock);
    }

    public function testAdd(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);
        
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        
        $result = $this->quizParticipant->add('Иван Петров', 25, 'IT', 1, 'easy');
        
        $this->assertNull($result);
    }

    public function testGetAll(): void
    {
        $expectedData = [
            ['id' => 1, 'name' => 'Иван', 'age' => 25, 'topic' => 'IT', 'prize' => 1, 'difficulty' => 'easy', 'created_at' => '2025-01-01 10:00:00'],
            ['id' => 2, 'name' => 'Мария', 'age' => 30, 'topic' => 'Science', 'prize' => 0, 'difficulty' => 'hard', 'created_at' => '2025-01-02 11:00:00']
        ];
        
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->willReturn($this->stmtMock);
        
        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedData);
        
        $result = $this->quizParticipant->getAll();
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('Иван', $result[0]['name']);
        $this->assertEquals('Мария', $result[1]['name']);
    }

    public function testGetById(): void
    {
        $expectedData = ['id' => 1, 'name' => 'Иван', 'age' => 25, 'topic' => 'IT', 'prize' => 1, 'difficulty' => 'easy'];
        
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);
        
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([1]);
        
        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn($expectedData);
        
        $result = $this->quizParticipant->getById(1);
        
        $this->assertIsArray($result);
        $this->assertEquals('Иван', $result['name']);
        $this->assertEquals(25, $result['age']);
    }

    public function testGetCount(): void
    {
        $expectedData = ['total' => 5];
        
        $this->pdoMock->expects($this->once())
            ->method('query')
            ->willReturn($this->stmtMock);
        
        $this->stmtMock->expects($this->once())
            ->method('fetch')
            ->willReturn($expectedData);
        
        $result = $this->quizParticipant->getCount();
        
        $this->assertEquals(5, $result);
    }

    public function testDelete(): void
    {
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);
        
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([1]);
        
        $this->quizParticipant->delete(1);
        
        $this->assertTrue(true);
    }

    public function testGetByAge(): void
    {
        $expectedData = [
            ['id' => 2, 'name' => 'Мария', 'age' => 30],
            ['id' => 3, 'name' => 'Петр', 'age' => 35]
        ];
        
        $this->pdoMock->expects($this->once())
            ->method('prepare')
            ->willReturn($this->stmtMock);
        
        $this->stmtMock->expects($this->once())
            ->method('execute')
            ->with([18]);
        
        $this->stmtMock->expects($this->once())
            ->method('fetchAll')
            ->willReturn($expectedData);
        
        $result = $this->quizParticipant->getByAge(18);
        
        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertGreaterThanOrEqual(18, $result[0]['age']);
    }
}