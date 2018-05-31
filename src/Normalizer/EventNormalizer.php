<?php

namespace App\Normalizer;

use App\Entity\Event;
use Symfony\Component\Serializer\Exception\BadMethodCallException;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Validator\Constraints\DateTime;

class EventNormalizer extends ObjectNormalizer
{
    public function normalize($object, $format = null, array $context = array())
    {
        $dateStart = null;
        $dateEnd = null;
        if (null !== $object->getDateStart()) {
            $dateStart = $object->getDateStart();//->format(\DateTime::ATOM);
        }

        if (null !== $object->getDateEnd()) {
            $dateEnd = $object->getDateEnd();//->format(\DateTime::ATOM);
        }

        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'hash' => $object->getHash(),
            'location' => $object->getLocation(),
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd,
            'coucou' => 'coucou',
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Event;
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (isset($data['dateStart']) && null !== $data['dateStart'] && is_string($data['dateStart'])) {
            $data['dateStart'] = new \DateTime($data['dateStart']);
        }

        if (isset($data['dateEnd']) && null !== $data['dateEnd'] && is_string($data['dateEnd'])) {
            $data['dateEnd'] = new \DateTime($data['dateEnd']);
        }
        return parent::denormalize($data, $class, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'App\Entity\Event';
    }

    /**
     * Extracts attributes to normalize from the class of the given object, format and context.
     *
     * @param object $object
     * @param string|null $format
     * @param array $context
     *
     * @return string[]
     */
    protected function extractAttributes($object, $format = null, array $context = array())
    {
        return parent::extractAttributes($object, $format, $context);
    }

    /**
     * Gets the attribute value.
     *
     * @param object $object
     * @param string $attribute
     * @param string|null $format
     * @param array $context
     *
     * @return mixed
     */
    protected function getAttributeValue($object, $attribute, $format = null, array $context = array())
    {
        return parent::getAttributeValue($object, $attribute, $format, $context);
    }

    /**
     * Sets attribute value.
     *
     * @param object $object
     * @param string $attribute
     * @param mixed $value
     * @param string|null $format
     * @param array $context
     */
    protected function setAttributeValue($object, $attribute, $value, $format = null, array $context = array())
    {
        parent::setAttributeValue($object, $attribute, $value, $format, $context);
    }
}