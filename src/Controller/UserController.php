<?php
/**
 * Created by PhpStorm.
 * User: MaximeMaillet
 * Date: 21/05/2018
 * Time: 16:07
 */

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends Controller
{
    /**
     * @IsGranted("ROLE_USER")
     * @param Request $request
     */
    public function getUsersAction(Request $request)
    {
        return new JsonResponse([
            'message' => 'ok'
        ]);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function getTestAction(Request $request)
    {
        return new JsonResponse([
            'message' => 'ok'
        ]);
    }
}