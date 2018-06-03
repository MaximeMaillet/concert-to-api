<?php

namespace App\Traits;

use App\Entity\User;
use App\Normalizer\EventNormalizer;
use App\Services\ApiService;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

trait SerializerTrait
{
    /**
     * @param $valid
     * @return JsonResponse
     */
    public function renderBoolean($valid)
    {
        return new JsonResponse(['success' => $valid], 200);
    }

    /**
     * @param FormInterface $form
     * @return JsonResponse
     */
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

    /**
     * @param $array
     * @param int $status
     * @param array $headers
     * @return JsonResponse
     */
    public function renderArray(array $array, $status = 200, $headers = [])
    {
        return new JsonResponse($array, $status, $headers);
    }

    /**
     * @param FormInterface $form
     * @param string $glue
     * @return string
     */
    private function extractErrorPath(FormInterface $form, $glue = '.')
    {
        $rootName = null;

        if (!$form->isRoot()) {
            $rootName = $this->extractErrorPath($form->getParent(), $glue);
        }

        return (!empty($rootName) ? $rootName . $glue : '') . $form->getName();
    }

    /**
     * @param $data
     * @param array $groups
     * @return array|bool|float|int|object|string
     */
    public function normalize($data, array $groups = [])
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $serializer = new Serializer([
            new EventNormalizer(),
            new ObjectNormalizer($classMetadataFactory),
        ]);
        return $serializer->normalize($data, null, ['groups' => $this->getApiGroups($groups)]);
    }

    /**
     * @param array $groups
     * @return array
     */
    public function getApiGroups($groups = [])
    {
        /** @var User $user */
        $user = null;

        try {
            $user = $this->getUser();
        } catch (\Exception $e) {
        }

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