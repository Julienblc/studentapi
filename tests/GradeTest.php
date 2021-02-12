<?php


namespace App\Tests;


use PHPUnit\Framework\TestCase;

class GradeTest extends TestCase
{
    public function testAddStudentGrade()
    {
        $requestBuilder = new RequestBuilder();
        // ADD new Student
        $studentResponse = $requestBuilder->post('/api/student', [
            'firstname' => 'TestFirstname',
            'lastname' => 'TestPUTLastname',
            'birthdate' => '1990-01-01'
        ]);

        // ADD grade to the Student
        $student = json_decode($studentResponse->getBody(), true);
        $gradeResponse = $requestBuilder->post('/api/grade/'.$student['id'], [
            'value' => 18,
            'subject' => 'math'
        ]);
        $this->assertEquals(201, $gradeResponse->getStatusCode());
        $grade = json_decode($gradeResponse->getBody(), true);
        $this->assertEquals(18, $grade['value']);
        $this->assertEquals('math', $grade['subject']);
        $this->assertEquals($student, $grade['student']);
    }

    public function testStudentAverage()
    {
        $requestBuilder = new RequestBuilder();
        // ADD new Student
        $studentResponse = $requestBuilder->post('/api/student', [
            'firstname' => 'TestFirstname',
            'lastname' => 'TestPUTLastname',
            'birthdate' => '1990-01-01'
        ]);

        // ADD 3 grades to the Student
        $student = json_decode($studentResponse->getBody(), true);
        $requestBuilder->post('/api/grade/'.$student['id'], [
            'value' => 10,
            'subject' => 'math'
        ]);
        $requestBuilder->post('/api/grade/'.$student['id'], [
            'value' => 4,
            'subject' => 'english'
        ]);
        $requestBuilder->post('/api/grade/'.$student['id'], [
            'value' => 3,
            'subject' => 'sport'
        ]);

        // GET average
        $averageResponse = $requestBuilder->get('/api/average-student/'.$student['id']);
        $this->assertEquals(200, $averageResponse->getStatusCode());
        $average = json_decode($averageResponse->getBody(), true);
        $this->assertEquals(5.67, $average['average']);
    }

    public function testClassAverage()
    {
        // GET class average (all Grades)
        $requestBuilder = new RequestBuilder();
        $averageResponse = $requestBuilder->get('/api/average-class');
        $this->assertEquals(200, $averageResponse->getStatusCode());
        $average = json_decode($averageResponse->getBody(), true);
        $this->assertGreaterThanOrEqual(0, $average['average']);
        $this->assertLessThanOrEqual(20, $average['average']);
    }

    /**
     * @afterClass
     */
    public static function tearDownGrades()
    {
        // Not working with Docker, working on it
        // exec('php bin/console app:clean-tests-datas');
    }
}