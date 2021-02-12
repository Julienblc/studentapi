<?php


namespace App\Controller;


use App\Entity\Grade;
use App\Service\ApiSerializer;
use App\Service\ResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class GradeController extends AbstractController
{
    private $em;
    private $serializer;
    private $responseBuilder;
    private $validator;

    public function __construct(EntityManagerInterface $em, ApiSerializer $serializer, ResponseBuilder $responseBuilder, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->em = $em;
        $this->responseBuilder = $responseBuilder;
        $this->validator = $validator;
    }

    /**
     * Create a grade.
     * @SWG\Parameter(
     *     in="formData",
     *     name="value",
     *     type="integer",
     *     required=true,
     *     description="Value of the grade, between 0 and 20 included."
     * )
     * @SWG\Parameter(
     *     in="formData",
     *     name="subject",
     *     type="string",
     *     required=true,
     *     description="Subject of the Grade."
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Returns the created Grade.",
     *     @Model(type=Grade::class)
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Student not found.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="error", type="string", example="student_not_found"),
     *          @SWG\Property(property="message", type="string", example="Student not found."),
     *      )
     * )
     * @SWG\Response(
     *     response=412,
     *     description="Bad form data.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="error", type="string", example="form_values_error"),
     *          @SWG\Property(property="message", type="string", example="This value should be less than or equal to 20."),
     *          @SWG\Property(property="property", type="string", example="value"),
     *      )
     * )
     * @param int $studentId
     * Id of the Student
     * @param Request $request
     * @return Response
     */
    public function create(int $studentId, Request $request): Response
    {
        // FIND Student
        $student = $this->em->getRepository('App:Student')->find($studentId);
        if ($student === null) {
            return $this->responseBuilder
                ->createResponse($this->serializer->serializeData(['error' => 'student_not_found', 'message' => 'Student not found.']), 404);
        }

        // VALIDATE form data
        $constraint = new Assert\Collection([
            'value' => [
                new Assert\GreaterThanOrEqual(0),
                new Assert\LessThanOrEqual(20)
            ],
            'subject' => [
                new Assert\NotBlank()
            ]
        ]);
        $validation = $this->responseBuilder->validateRequest($constraint, $request->request->all());
        if ($validation instanceof Response) return $validation;

        // CREATE new Grade
        $newGrade = new Grade();
        $newGrade->setValue($request->get('value'));
        $newGrade->setSubject($request->get('subject'));
        $newGrade->setStudent($student);
        $this->em->persist($newGrade);
        $this->em->flush();

        return $this->responseBuilder
            ->createResponse($this->serializer->serializeData($newGrade), 201);
    }

    /**
     * Get the average of grades of a student.
     * @SWG\Response(
     *     response=200,
     *     description="Returns the average (rounded to the nearest hundreth) of all Grades of a Student.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="average", type="integer", example="12,5"),
     *      )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="Student or Grades not found.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="error", type="string", example="student_not_found"),
     *          @SWG\Property(property="message", type="string", example="Student not found."),
     *      )
     * )
     * @param int $studentId
     * Id of the Student
     * @return Response
     */
    public function studentAverage(int $studentId): Response
    {
        // FIND Student
        $student = $this->em->getRepository('App:Student')->find($studentId);
        if ($student === null) {
            return $this->responseBuilder
                ->createResponse($this->serializer->serializeData(['error' => 'student_not_found', 'message' => 'Student not found.']), 404);
        }

        // GET all student grades
        $studentGrades = $this->em->getRepository('App:Grade')->getAllGradesByStudent($student);
        if (count($studentGrades) === 0) {
            return $this->responseBuilder
                ->createResponse($this->serializer->serializeData(['error' => 'student_grade_not_found', 'message' => 'No student grade found.']), 404);

        }

        // MAKE average
        $gradesArray = [];
        foreach ($studentGrades as $studentGrade) {
            $gradesArray[] = $studentGrade->getValue();
        }

        return $this->responseBuilder
            ->createResponse($this->serializer->serializeData($this->makeAverage($gradesArray)));
    }

    /**
     * Get the average of all grades.
     * @SWG\Response(
     *     response=200,
     *     description="Returns the average (rounded to the nearest hundreth) of all Grades stored.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="average", type="integer", example="12,5"),
     *      )
     * )
     * @SWG\Response(
     *     response=404,
     *     description="No class grade found.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="error", type="string", example="class_grade_not_found"),
     *          @SWG\Property(property="message", type="string", example="No class grade found."),
     *      )
     * )
     * @return Response
     */
    public function classAverage(): Response
    {
        // GET all grades
        $allGrades = $this->em->getRepository('App:Grade')->findAll();

        if (count($allGrades) === 0) {
            return $this->responseBuilder
                ->createResponse($this->serializer->serializeData(['error' => 'class_grade_not_found', 'message' => 'No class grade found.']), 404);
        }

        // MAKE average
        $gradesArray = [];
        foreach ($allGrades as $grade) {
            $gradesArray[] = $grade->getValue();
        }

        return $this->responseBuilder
            ->createResponse($this->serializer->serializeData($this->makeAverage($gradesArray)));
    }

    /**
     * @param array $grades
     * @return int[]
     */
    private function makeAverage(array $grades): array
    {
        $countGrades = count($grades);
        if ($countGrades === 0) {
            $average = null;
        } else {
            $average = round(array_sum($grades)/$countGrades, 2);
        }
        return [
            "average" => $average
        ];
    }
}