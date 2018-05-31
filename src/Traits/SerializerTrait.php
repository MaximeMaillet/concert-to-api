<?php

namespace App\Traits;

use App\Entity\User;
use App\Normalizer\EventNormalizer;
use App\Services\ApiService;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

trait SerializerTrait
{
    public function renderBoolean($valid)
    {
        return new JsonResponse(['success' => $valid], 200);
    }

    public function renderFormErrors(FormInterface $form)
    {
        $errors = [];
        $formErr = $form->getErrors(true);

        foreach($formErr as $key => $error) {
            $errors[] = [
                'field' => $this->extractErrorPath($error->getOrigin()),
                'message' => $error->getMessage()
            ];
        }

        return new JsonResponse($errors, 422);
    }

    private function extractErrorPath(FormInterface $form, $glue = '.')
    {
        $rootName = null;

        if (!$form->isRoot()) {
            $rootName = $this->extractErrorPath($form->getParent(), $glue);
        }

        return (!empty($rootName) ? $rootName . $glue : '') . $form->getName();
    }

    public function deserialize($data, $class, $groups = [])
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $serializer = new Serializer(
            [
                new EventNormalizer(),
                new ObjectNormalizer($classMetadataFactory),
            ],
            [
                new JsonEncoder()
            ]
        );
        return $serializer->deserialize($data, $class, 'json');
    }

    public function serialize($data, array $groups = [], int $status = 200, array $headers = [])
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $serializer = new Serializer([
            new EventNormalizer(),
            new ObjectNormalizer($classMetadataFactory),
        ]);
        return $serializer->normalize($data, null, ['groups' => $this->getApiGroups($groups)]);
    }

    public function getApiGroups($groups = [])
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($user === null) {
            $groups[] = ApiService::API_GROUP_NO_AUTH;
            return $groups;
        }

        $groups[] = ApiService::API_GROUP_AUTH;

        if ($user->hasRole(User::ROLE_ADMIN) || $user->hasRole(User::ROLE_SUPER_ADMIN)) {
            $groups[] = ApiService::API_GROUP_ADMIN;
        }

        return $groups;
    }
}