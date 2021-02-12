<?php


namespace App\Tests;

use App\Tests\RequestBuilder;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;

class StudentTest extends TestCase
{
    public function testAddStudent()
    {
        $requestBuilder = new RequestBuilder();
        // ADD new Student
        $response = $requestBuilder->post('/api/student', [
            'firstname' => 'TestFirstname',
            'lastname' => 'TestLastname',
            'birthdate' => '1990-01-01'
        ]);
        $this->assertEquals(201, $response->getStatusCode());
        $student = json_decode($response->getBody(), true);
        $this->assertEquals('TestFirstname', $student['firstname']);
        $this->assertEquals('TestLastname', $student['lastname']);
        $this->assertEquals('1990-01-01T00:00:00+00:00', $student['birthdate']);
    }

    public function testAddStudentError()
    {
        $requestBuilder = new RequestBuilder();
        // ADD wrong new Student
        $response = $requestBuilder->post('/api/student', [
            'firstname' => '',
            'lastname' => 'TestLastname',
            'birthdate' => '1990-01-01'
        ]);
        $this->assertEquals(412, $response->getStatusCode());
        $student = json_decode($response->getBody(), true)[0];
        $this->assertEquals('form_values_error', $student['error']);
        $this->assertEquals('firstname', $student['property']);
    }

    public function testUpdateStudent()
    {
        $requestBuilder = new RequestBuilder();
        // ADD new Student
        $response = $requestBuilder->post('/api/student', [
            'firstname' => 'TestFirstname',
            'lastname' => 'TestPUTLastname',
            'birthdate' => '1990-01-01'
        ]);

        // UPDATE Student
        $data = json_decode($response->getBody(), true);
        $response = $requestBuilder->put('/api/student/'.$data['id'], [
            'firstname' => 'TestFirstname',
            'lastname' => 'TestPUTLastnameAFTER',
            'birthdate' => '1980-01-01'
        ]);
        $this->assertEquals(200, $response->getStatusCode());
        $student = json_decode($response->getBody(), true);
        $this->assertEquals('TestFirstname', $student['firstname']);
        $this->assertEquals('TestPUTLastnameAFTER', $student['lastname']);
        $this->assertEquals('1980-01-01T00:00:00+00:00', $student['birthdate']);
    }

    public function testDeleteStudent()
    {
        $requestBuilder = new RequestBuilder();
        // ADD new Student
        $response = $requestBuilder->post('/api/student', [
            'firstname' => 'TestFirstname',
            'lastname' => 'TestPUTLastname',
            'birthdate' => '1990-01-01'
        ]);

        // DELETE student
        $student = json_decode($response->getBody(), true);
        $response = $requestBuilder->delete('/api/student/'.$student['id']);
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * @afterClass
     */
    public static function tearDownStudents()
    {
        // Not working with Docker, working on it
        // exec('php bin/console app:clean-tests-datas');
    }
}