<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
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
     * @return JsonResponse
     */
    public function loginAction()
    {
        return new JsonResponse(['status' => 'login']);
    }
}