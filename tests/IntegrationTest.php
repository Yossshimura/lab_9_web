<?php

use PHPUnit\Framework\TestCase;

class IntegrationTest extends TestCase
{
    private $pdo;
    private $quizParticipant;

    protected function setUp(): void
    {
        $host = 'db';
        $db = 'test_db';
        $user = 'test_user';
        $pass = 'test_pass';
        
        try {
            $this->pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            $this->pdo->exec("
                CREATE TABLE IF NOT EXISTS quiz_participants (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    name VARCHAR(100) NOT NULL,
                    age INT NOT NULL,
                    topic VARCHAR(100) NOT NULL,
                    prize TINYINT(1) DEFAULT 0,
                    difficulty VARCHAR(50) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )
            ");
            
            $this->pdo->exec("TRUNCATE TABLE quiz_participants");
        } catch (PDOException $e) {
            $this->markTestSkipped('Тестовая БД не доступна: ' . $e->getMessage());
        }
        
        require_once __DIR__ . '/../www/QuizParticipant.php';
        $this->quizParticipant = new QuizParticipant($this->pdo);
    }

    protected function tearDown(): void
    {
        if ($this->pdo) {
            $this->pdo->exec("TRUNCATE TABLE quiz_participants");
        }
    }

    public function testAddAndGetInRealDatabase(): void
    {
        $this->quizParticipant->add('Тестовый Участник', 25, 'IT', 1, 'easy');
        
        $all = $this->quizParticipant->getAll();
        
        $this->assertCount(1, $all);
        $this->assertEquals('Тестовый Участник', $all[0]['name']);
        $this->assertEquals(25, $all[0]['age']);
    }

    public function testGetCountInRealDatabase(): void
    {
        $this->quizParticipant->add('Участник 1', 20, 'History', 0, 'medium');
        $this->quizParticipant->add('Участник 2', 30, 'Science', 1, 'hard');
        
        $count = $this->quizParticipant->getCount();
        
        $this->assertEquals(2, $count);
    }

    public function testFilterByAgeInRealDatabase(): void
    {
        $this->quizParticipant->add('Молодой', 16, 'IT', 0, 'easy');
        $this->quizParticipant->add('Взрослый', 25, 'IT', 1, 'medium');
        $this->quizParticipant->add('Пожилой', 35, 'History', 0, 'hard');
        
        $adults = $this->quizParticipant->getByAge(18);
        
        $this->assertCount(2, $adults);
        foreach ($adults as $adult) {
            $this->assertGreaterThanOrEqual(18, $adult['age']);
        }
    }

    public function testDeleteInRealDatabase(): void
    {
        $this->quizParticipant->add('Участник для удаления', 25, 'IT', 0, 'easy');
        
        $countBefore = $this->quizParticipant->getCount();
        $this->assertEquals(1, $countBefore);
        
        $this->quizParticipant->delete(1);
        
        $countAfter = $this->quizParticipant->getCount();
        $this->assertEquals(0, $countAfter);
    }

    public function testInvalidDataHandling(): void
    {
        $this->expectException(PDOException::class);
        
        $this->quizParticipant->add('', -5, '', 2, 'invalid');
    }
}