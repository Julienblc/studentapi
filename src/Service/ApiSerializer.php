<?php


namespace App\Service;


use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

class ApiSerializer
{
    protected $jmsSerializer;

    public function __construct(SerializerInterface $jmsSerializer)
    {
        $this->jmsSerializer = $jmsSerializer;
    }

    public function serializeData($data, $groups = ['api'], $format = 'json'): string
    {
        $context = new SerializationContext();
        $context->setSerializeNull(true);
        $context->setGroups($groups);

        return $this->jmsSerializer->serialize($data, $format, $context);
    }
}