<?php


namespace App\Command;


use App\Entity\Grade;
use App\Entity\Student;
use DateTime;
use DateTimeZone;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateUserAndGradesCommand extends Command
{
    private $entityManager;
    protected static $defaultName = 'app:create-user-grades';

    private $students = [
        [
            'firstname' => 'Frodo',
            'lastname' => 'Baggins',
            'birthdate' => '1991-07-24'
        ],
        [
            'firstname' => 'Samsagace',
            'lastname' => 'Gamegie',
            'birthdate' => '1992-06-20'
        ],
        [
            'firstname' => 'Meriadoc',
            'lastname' => 'Brandebouc',
            'birthdate' => '1993-05-18'
        ],
        [
            'firstname' => 'Peregrin',
            'lastname' => 'Touque',
            'birthdate' => '1994-04-13'
        ]
    ];

    private $gradeSubjects = [
        "math",
        "english",
        "art",
        "sport",
        "Philosophy"
    ];

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Creates new users and new grades')
            ->setHelp('This command creates new users and new grades.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $em = $this->entityManager;
        $gradeSubjectsArraySize = count($this->gradeSubjects);
        foreach ($this->students as $student) {
            echo(sprintf("Creating %s %s \r\n", $student['firstname'], $student['lastname']));
            $newStudent = new Student();
            $newStudent->setFirstname($student['firstname']);
            $newStudent->setLastname($student['lastname']);
            try {
                $newStudent->setBirthdate(new DateTime($student['birthdate']));
            } catch (Exception $e) {}
            $em->persist($newStudent);
            // Create two fake grades
            echo(sprintf("Creating grades for %s %s \r\n", $student['firstname'], $student['lastname']));
            $firstNewGrade = new Grade();
            $secondNewGrade = new Grade();
            $firstNewGrade->setStudent($newStudent);
            $secondNewGrade->setStudent($newStudent);
            try {
                $firstNewGrade->setSubject($this->gradeSubjects[random_int(0, $gradeSubjectsArraySize - 1)]);
                $firstNewGrade->setValue(random_int(0, 20));
                $secondNewGrade->setSubject($this->gradeSubjects[random_int(0, $gradeSubjectsArraySize - 1)]);
                $secondNewGrade->setValue(random_int(0, 20));
            } catch (Exception $e) {}
            $em->persist($firstNewGrade);
            $em->persist($secondNewGrade);
        }
        $em->flush();
        echo "DONE";
        return Command::SUCCESS;
    }
}