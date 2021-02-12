<?php


namespace App\Controller;


use App\Entity\Student;
use App\Service\ApiSerializer;
use App\Service\ResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;

class StudentController extends AbstractController
{
    private $em;
    private $serializer;
    private $responseBuilder;
    private $validator;
    private $studentConstraints;

    public function __construct(EntityManagerInterface $em, ApiSerializer $serializer, ResponseBuilder $responseBuilder, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->em = $em;
        $this->responseBuilder = $responseBuilder;
        $this->validator = $validator;
        $this->studentConstraints = new Assert\Collection([
            'firstname' => [
                new Assert\NotBlank()
            ],
            'lastname' => [
                new Assert\NotBlank()
            ],
            'birthdate' => [
                new Assert\NotBlank(),
                new Assert\Date()
            ]
        ]);
    }

    /**
     * Create a new Student.
     * @SWG\Parameter(
     *     in="formData",
     *     name="firstname",
     *     type="string",
     *     required=true,
     *     description="Firstname of the Student."
     * )
     * @SWG\Parameter(
     *     in="formData",
     *     name="lastname",
     *     type="string",
     *     required=true,
     *     description="Lastname of the Student."
     * )
     * @SWG\Parameter(
     *     in="formData",
     *     name="birthdate",
     *     type="string",
     *     required=true,
     *     description="Birthdate of the Student in format YYYY-MM-DD."
     * )
     * @SWG\Response(
     *     response=201,
     *     description="Returns the created Student.",
     *     @Model(type=Student::class)
     * )
     * @SWG\Response(
     *     response=412,
     *     description="Bad form data.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="error", type="string", example="form_values_error"),
     *          @SWG\Property(property="message", type="string", example="This value should not be blank."),
     *          @SWG\Property(property="property", type="string", example="firstname"),
     *      )
     * )
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function create(Request $request): Response
    {
        // VALIDATE form data
        $formData = $request->request->all();
        $validation = $this->responseBuilder->validateRequest($this->studentConstraints, $formData);
        if ($validation instanceof Response) return $validation;

        // Need to create a DateTime object to validate the birthdate constraint "less than today"
        $birthdateDateTime = [
            "birthdate" => new \DateTime($formData['birthdate'])
        ];
        $validation = $this->responseBuilder->validateRequest(
            new Assert\Collection([
                'birthdate' => [new Assert\LessThan('today')
                ]
        ]), $birthdateDateTime);
        if ($validation instanceof Response) return $validation;

        // CREATE new student
        $newStudent = new Student();
        $newStudent->setFirstname($formData['firstname']);
        $newStudent->setLastname($formData['lastname']);
        $newStudent->setBirthdate($birthdateDateTime['birthdate']);
        $this->em->persist($newStudent);
        $this->em->flush();

        return $this->responseBuilder
            ->createResponse($this->serializer->serializeData($newStudent), 201);
    }

    /**
     * Update a Student informations.
     * @SWG\Parameter(
     *      name="body",
     *      in="body",
     *      description="JSON Payload (birthdate in format YYYY-MM-DD).",
     *      required=true,
     *      format="application/json",
     *      @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="firstname", type="string", example="John"),
     *          @SWG\Property(property="lastname", type="string", example="Doe"),
     *          @SWG\Property(property="birthdate", type="string", example="1990-01-01"),
     *      )
     * )
     * @SWG\Response(
     *     response=200,
     *     description="Returns the updated Student.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="id", type="integer", example="42"),
     *          @SWG\Property(property="firstname", type="string", example="John"),
     *          @SWG\Property(property="lastname", type="string", example="Doe"),
     *          @SWG\Property(property="birthdate", type="string", example="1990-01-01"),
     *      )
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
     *     description="Bad JSON payload.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="error", type="string", example="form_values_error"),
     *          @SWG\Property(property="message", type="string", example="This value should not be blank."),
     *          @SWG\Property(property="property", type="string", example="firstname"),
     *      )
     * )
     * @param int $id
     * Id of the Student
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function update(int $id, Request $request): Response
    {
        // FIND student
        $student = $this->em->getRepository('App:Student')->find($id);
        if ($student === null) {
            return $this->responseBuilder
                ->createResponse($this->serializer->serializeData([
                    'error' => 'student_not_found',
                    'message' => 'Student not found.'
                ]), 404);
        }

        // VALIDATE json
        $data = get_object_vars(json_decode($request->getContent()));
        $validation = $this->responseBuilder->validateRequest($this->studentConstraints, $data);
        if ($validation instanceof Response) return $validation;

        // Need to create a DateTime object to validate the birthdate constraint "less than today"
        $birthdateDateTime = [
            "birthdate" => new \DateTime($data['birthdate'])
        ];
        $validation = $this->responseBuilder->validateRequest(
            new Assert\Collection([
                'birthdate' => [new Assert\LessThan('today')
                ]
            ]), $birthdateDateTime);
        if ($validation instanceof Response) return $validation;

        // UPDATE student
        $student->setFirstname($data['firstname']);
        $student->setLastname($data['lastname']);
        try {
            $student->setBirthdate(new \DateTime($data['birthdate']));
        } catch (Exception $e) {}
        $this->em->flush();
        return $this->responseBuilder
            ->createResponse($this->serializer->serializeData($student));
    }

    /**
     * Delete a Student.
     * @SWG\Response(
     *     response=200,
     *     description="Returns OK if deleted.",
     *     @SWG\Schema(
     *          type="object",
     *          @SWG\Property(property="ok", type="string", example="Student deleted.")
     *      )
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
     * @param int $id
     * Id of the Student
     * @return Response
     */
    public function delete(int $id): Response
    {
        // FIND Student
        $student = $this->em->getRepository('App:Student')->find($id);
        if ($student === null) {
            return $this->responseBuilder
                ->createResponse($this->serializer->serializeData([
                    'error' => 'student_not_found',
                    'message' => 'Student not found.'
                ]), 404);
        }

        // REMOVE Student
        $this->em->remove($student);
        $this->em->flush();
        return $this->responseBuilder
            ->createResponse($this->serializer->serializeData(['OK' => 'Student deleted.']));
    }
}