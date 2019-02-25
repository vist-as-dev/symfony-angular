<?php

namespace App\Controller;

use App\Service\SecurityService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SecurityController
 * @package App\Controller
 *
 */
class SecurityController
{
    /**
     * @Route("/register", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function registerAction()
    {
        return new JsonResponse(['status' => 'register']);
    }

    /**
     * @Route("/confirm", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function confirmAction()
    {
        return new JsonResponse(['status' => 'confirm']);
    }

    /**
     * @Route("/forgot", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function forgotAction()
    {
        return new JsonResponse(['status' => 'forgot']);
    }

    /**
     * @Route("/reset", methods={"POST"})
     *
     * @return JsonResponse
     */
    public function resetAction()
    {
        return new JsonResponse(['status' => 'reset']);
    }

    /**
     * @Route("/login", methods={"POST"})
     *
     * @param Request $request
     * @param SecurityService $security
     * @return JsonResponse
     */
    public function loginAction(Request $request, SecurityService $security)
    {
        try {
            ['email' => $email, 'password' => $password] = json_decode($request->getContent(), true);

            return new JsonResponse(['token' => $security->login($email, $password)]);
        } catch (\Exception $e) {
            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}