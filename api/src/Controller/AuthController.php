<?php

namespace App\Controller;

use App\Service\SecurityService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AuthController
 * @package App\Controller
 */
class AuthController extends Controller
{
    /**
     * Affiliates and management login
     *
     * @Route("/login", name="login")
     * @Method({"POST"})
     *
     * @param Request $request
     * @param SecurityService $auth
     * @return JsonResponse
     */
    public function login(Request $request, SecurityService $auth)
    {
        try {
            $token = $auth->login(
                $auth->getEnvironment($request->getHost()),
                json_decode($request->getContent(), true)
            );
            return $this->json(['message' => 'You are logged.', 'token' => (string)$token]);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Affiliate registration
     *
     * @Route("/signup", name="signup")
     * @Method({"POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Affiliate registration"
     * )
     * @SWG\Tag(name="authorization")
     *
     * @param Request $request
     * @param SecurityService $auth
     * @return JsonResponse
     */
    public function signup(Request $request, SecurityService $auth)
    {
        try {
            $auth->signup(
                $auth->getEnvironment($request->getHost()),
                json_decode($request->getContent(), true)
            );
            return $this->json(['message' => 'You are registered. Check your email.']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Confirm affiliate email
     *
     * @Route("/signup/{token}", name="signupConfirm", requirements={"token"="[\S]+"})
     * @Method({"GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Confirm affiliate email"
     * )
     * @SWG\Tag(name="authorization")
     *
     * @param Request $request
     * @param SecurityService $auth
     * @param $token
     * @return JsonResponse
     */
    public function signupConfirm(Request $request, SecurityService $auth, $token)
    {
        try {
            $auth->signupConfirm($auth->getEnvironment($request->getHost()), $token);
            return $this->json(['message' => 'Your email is confirmed. Please log in.']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Forgot password. Send email with reset password link.
     *
     * @Route("/forgot", name="forgot")
     * @Method({"POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Forgot password. Send email with reset password link."
     * )
     * @SWG\Tag(name="authorization")
     *
     * @param Request $request
     * @param SecurityService $auth
     * @return JsonResponse
     */
    public function forgot(Request $request, SecurityService $auth)
    {
        try {
            $auth->forgot(
                $auth->getEnvironment($request->getHost()),
                json_decode($request->getContent(), true)
            );
            return $this->json(['message' => 'Success. Check your email.']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Reset password
     *
     * @Route("/reset/{token}", name="reset", requirements={"token"="[\S]+"})
     * @Method({"POST"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Reset password"
     * )
     * @SWG\Tag(name="authorization")
     *
     * @param Request $request
     * @param SecurityService $auth
     * @param $token
     * @return JsonResponse
     */
    public function reset(Request $request, SecurityService $auth, $token)
    {
        try {
            $auth->reset(
                $auth->getEnvironment($request->getHost()),
                $token,
                json_decode($request->getContent(), true)
            );
            return $this->json(['message' => 'New password has been set.']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Create new environment
     *
     * @Route("/environment", name="create")
     * @Method({"PUT"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Create new environment"
     * )
     * @SWG\Tag(name="authorization")
     *
     * @param Request $request
     * @param SecurityService $auth
     * @return JsonResponse
     */
    public function createEnvironment(Request $request, SecurityService $auth)
    {
        try {
            $auth->createEnvironment(
                $auth->getEnvironment($request->getHost()),
                json_decode($request->getContent(), true)
            );
            return $this->json(['message' => 'Your environment has been created. Check your email.']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Confirm super admin email for new environment
     *
     * @Route("/environment/{token}", name="createConfirm", requirements={"token"="[\S]+"})
     * @Method({"GET"})
     *
     * @SWG\Response(
     *     response=200,
     *     description="Confirm super admin email for new environment"
     * )
     * @SWG\Tag(name="authorization")
     *
     * @param SecurityService $auth
     * @param $token
     * @return JsonResponse
     */
    public function confirmCreateEnvironment(SecurityService $auth, $token)
    {
        try {
            $auth->confirmCreateEnvironment($token);
            return $this->json(['message' => 'Your email is confirmed.']);
        } catch (\Exception $e) {
            return $this->json(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}