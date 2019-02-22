<?php

namespace App\Security;

use App\Entity\User;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Lcobucci\JWT\Token;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    public function supports(Request $request)
    {
        if ($request->getMethod() === 'OPTIONS') {
            throw new HttpException(Response::HTTP_NO_CONTENT);
        }

        return $request->headers->has('X-AUTH-TOKEN');
    }

    public function getCredentials(Request $request)
    {
        try {
            $token = (new Parser())->parse((string) $request->headers->get('X-AUTH-TOKEN'));
        } catch (\Exception $e) {
            return [];
        }

        $credentials = [
            'token' => $token,
        ];

        return $credentials;
    }

    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        if ($credentials['token'] instanceof Token) {
            return $userProvider->loadUserByUsername($credentials['token']->getHeader('jti'));
        }
        return null;
    }

    public function checkCredentials($credentials, UserInterface $user)
    {
        /** @var Token $token */
        $token = $credentials['token'] ?? null;
        $signer = new Sha256();

        /** @var User $user */
        return
            $token instanceof Token
            && $token->verify($signer, $secret_key)
//            && $token->getClaim('exp') > time()
            && $user->getEnvironment()->getDomain() == ($credentials['domain'] ?? null)
            && $user->getEnvironment()->isEnabled()
        ;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $e)
    {
        $data = ['message' => strtr($e->getMessageKey(), $e->getMessageData())];
        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        if ($request->getMethod() !== 'OPTIONS') {
            return new JsonResponse(['message' => 'Authentication Required'], Response::HTTP_UNAUTHORIZED);
        } else {
            return new JsonResponse('No Content', Response::HTTP_NO_CONTENT);
        }
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
