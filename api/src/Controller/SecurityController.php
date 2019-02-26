<?php

namespace App\Controller;

use App\Controller\Controller;
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
class SecurityController extends Controller
{
    /**
     * @Route("/register", methods={"POST"})
     *
     * @param Request $request
     * @param SecurityService $security
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function registerAction(Request $request, SecurityService $security)
    {
        ['email' => $email] = $this->getJsonContent($request);

        $security->register($email);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }


    /**
     * @Route("/forgot", methods={"POST"})
     *
     * @param Request $request
     * @param SecurityService $security
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function forgotAction(Request $request, SecurityService $security)
    {
        ['email' => $email] = $this->getJsonContent($request);

        $security->forgot($email);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/reset", methods={"POST"})
     *
     * @param Request $request
     * @param SecurityService $security
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function resetAction(Request $request, SecurityService $security)
    {
        ['token' => $token, 'password' => $password] = $this->getJsonContent($request);

        $security->reset($token, $password);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/login", methods={"POST"})
     *
     * @param Request $request
     * @param SecurityService $security
     *
     * @return JsonResponse
     * @throws \Exception
     */
    public function loginAction(Request $request, SecurityService $security)
    {
        ['email' => $email, 'password' => $password] = $this->getJsonContent($request);

        return $this->json(['token' => $security->login($email, $password)]);
    }
}