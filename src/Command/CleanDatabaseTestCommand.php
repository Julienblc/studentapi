<?php


namespace App\Command;


use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanDatabaseTestCommand extends Command
{
    private $entityManager;
    protected static $defaultName = 'app:clean-tests-datas';

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setDescription('Remove all PHPUnit test datas')
            ->setHelp('This command remove all datas used in PHPUnit tests.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $testStudents = $this->entityManager->getRepository('App:Student')->getAllStudentsByFirstname('TestFirstname');
        foreach ($testStudents as $student) {
            $this->entityManager->remove($student);
        }
        $this->entityManager->flush();
        echo "DONE";
        return Command::SUCCESS;
    }
}