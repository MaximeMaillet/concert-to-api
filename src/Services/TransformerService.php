<?php
/**
 * Created by PhpStorm.
 * User: MaximeMaillet
 * Date: 21/05/2018
 * Time: 19:15
 */

namespace App\Services;


//use JMS\Serializer\SerializationContext;
//use JMS\Serializer\Serializer;

class TransformerService
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * TransformerService constructor.
     */
    public function __construct()
    {

    }

    public function transform($data, $groups = null)
    {
//        $context = SerializationContext::create();
//        if (!empty($groups)) {
//            $context->setGroups($groups);
//        }
//        return $this->serializer->serialize($data, 'json', $context);
    }
}