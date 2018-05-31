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
            'dateStart' => $object->getDateStart() ? $object->getDateStart()->format(\DateTime::ATOM) : null,
            'dateEnd' => $object->getDateEnd() ? $object->getDateEnd()->format(\DateTime::ATOM) : null,
            'artists' => $artists,
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

        $normalizer = new ObjectNormalizer();

        return $normalizer->denormalize($data, $class, $format, $context);
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === 'App\Entity\Event';
    }
}