<?php

namespace App\Security;

use App\Entity\User;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class TokenAuthenticator extends AbstractGuardAuthenticator
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function supports(Request $request)
    {
        return $request->headers->has('X-AUTH-TOKEN');
    }

    public function getCredentials(Request $request)
    {
        return [
            'token' => (new Parser())->parse($request->headers->get('X-AUTH-TOKEN')),
        ];
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
        if (!($user instanceof User)) {
            return null;
        }

        if (!($credentials['token'] instanceof Token)) {
            return null;
        }

        if (!$credentials['token']->verify(new Sha256(), $user->getSalt())) {
            return null;
        }

        if (time() > ($user->getLastActivityTimestamp() + getenv('USER_TOKEN_LIFETIME'))) {
            return null;
        } else {
            $user->setLastActivity(new \DateTime());
            $this->em->flush();
        }

        return true;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $e)
    {
        $data = [
            'message' => strtr($e->getMessageKey(), $e->getMessageData()),
        ];

        return new JsonResponse($data, Response::HTTP_FORBIDDEN);
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }

    public function start(Request $request, AuthenticationException $authException = null)
    {
        $data = [
            'message' => 'Authentication Required',
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }

    public function supportsRememberMe()
    {
        return false;
    }
}
