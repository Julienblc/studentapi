<?php


namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validation;

class ResponseBuilder
{
    private $serializer;

    public function __construct(ApiSerializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function createResponse($data, $statusCode = null): Response
    {
        $response = new Response($data);
        $response->headers->set('Content-Type', 'application/json');
        if ($statusCode) {
            $response->setStatusCode($statusCode);
        }
        return $response;
    }

    public function validateRequest(Collection $assertCollection, array $values): ?Response
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($values, $assertCollection);
        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[] = [
                    'error' => 'form_values_error',
                    'message' => $violation->getMessage(),
                    'property' => substr($violation->getPropertyPath(), 1, strlen($violation->getPropertyPath()) - 2)
                ];
            }
            return $this->createResponse($this->serializer->serializeData($errors), 412);
        }
        return null;
    }
}