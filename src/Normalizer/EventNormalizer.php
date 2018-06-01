<?php

namespace App\Normalizer;

use App\Entity\Event;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class EventNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize($object, $format = null, array $context = array())
    {
        $artists = [];
        foreach ($object->getArtists() as $artist) {
            $artists[] = [
                'id' => $artist->getId(),
                'name' => $artist->getName(),
                'logo' => $artist->getLogo(),
            ];
        }

        return [
            'id' => $object->getId(),
            'name' => $object->getName(),
            'hash' => $object->getHash(),
            'location' => $object->getLocation(),
            'startDate' => $object->getStartDate() ? $object->getStartDate()->format(\DateTime::ATOM) : null,
            'endDate' => $object->getEndDate() ? $object->getEndDate()->format(\DateTime::ATOM) : null,
            'artists' => $artists,
        ];
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Event;
    }

    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (isset($data['startDate']) && null !== $data['startDate'] && is_string($data['startDate'])) {
            $data['startDate'] = new \DateTime($data['startDate']);
        }

        if (isset($data['endDate']) && null !== $data['endDate'] && is_string($data['endDate'])) {
            $data['endDate'] = new \DateTime($data['endDate']);
        }

        $normalizer = new ObjectNormalizer();

        return $normalizer->denormalize($data, $class, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'App\Entity\Event';
    }
}