<?php

namespace App\Service;

use App\Entity\User;
use App\Exception\Request\BadRequestException;
use App\Exception\Security\AccessDeniedException;
use App\Exception\Security\ConfirmationTokenExpiredException;
use App\Exception\Security\ConfirmationTokenNotFoundException;
use App\Exception\Security\EmailAlreadyRegisteredException;
use App\Exception\Security\PasswordRequiredException;
use App\Exception\Security\UserNotFoundException;
use App\Service\Traits\TraitEncoder;
use App\Service\Traits\TraitLogger;
use App\Service\Traits\TraitMailer;
use App\Service\Traits\TraitRequest;
use App\Service\Traits\TraitTwig;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Swift_Mailer;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Twig_Environment;

class SecurityService extends AbstractService
{
    use
        TraitRequest,
        TraitEncoder,
        TraitMailer,
        TraitTwig,
        TraitLogger;

    const CONFIRMATION_TOKEN_PATTERN = '/^[a-z,A-Z,0-9,!/.+-@#$&*]{32,}$/';
    const USER_PASSWORD_PATTERN = '/^(?=.*[A-Z])(?=.*[!@#%$&*])(?=.*[0-9])(?=.*[a-z])[a-z,A-Z,0-9,!@#%$&*]{8,}$/';

    /**
     * AuthService constructor.
     * @param RequestStack $requestStack
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @param Swift_Mailer $mailer
     * @param Twig_Environment $twig
     */
    public function __construct(RequestStack $requestStack,
                                LoggerInterface $logger,
                                EntityManagerInterface $em,
                                UserPasswordEncoderInterface $encoder,
                                Swift_Mailer $mailer,
                                Twig_Environment $twig)
    {
        $this->setRequest($requestStack->getCurrentRequest());
        $this->setEm($em);
        $this->setLogger($logger);
        $this->setEncoder($encoder);
        $this->setMailer($mailer);
        $this->setTwig($twig);
    }

    /**
     * @param string $email
     *
     * @throws \Exception
     */
    public function register(string $email)
    {
        $this->validateEmail($email, true);

        $user = new User();
        $user->setEmail($email);
        $user->setConfirmationToken($this->createConfirmationToken());
        $user->setLastActivity(new \DateTime());

        $this->getEm()->persist($user);
        $this->getEm()->flush();

        $this->sendRegisterEmail($user);
    }

    /**
     * @param string $email
     * @param string $password
     *
     * @return Token
     * @throws \Exception
     */
    public function login(string $email, string $password)
    {
        $this->validateEmail($email);
        $this->validatePassword($password);

        /** @var User $user */
        $user = $this->getEm()->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new UserNotFoundException();
        }

        if (!$this->getEncoder()->isPasswordValid($user, $password)) {
            throw new AccessDeniedException();
        }

        $user->setLastActivity(new \DateTime());
        $user->setSalt(random_bytes(8));

        $this->getEm()->flush();

        return (new Builder())
            ->setIssuer($this->getRequest()->getHost())
            ->setAudience($this->getRequest()->getHost())
            ->setId($user->getId(), true)
            ->setIssuedAt(time())
            ->setNotBefore(time() + 0)
            ->setExpiration(time() + 3600)
            ->sign(new Sha256(), $user->getSalt())
            ->getToken();
    }

    /**
     * @param string $email
     * @throws \Exception
     */
    public function forgot(string $email)
    {
        $this->validateEmail($email);

        /** @var User $user */
        $user = $this->getEm()->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new UserNotFoundException();
        }

        $user->setLastActivity(new \DateTime());
        $user->setConfirmationToken($this->createConfirmationToken());

        $this->getEm()->flush();

        $this->sendForgotEmail($user);
    }

    /**
     * @param string $token
     * @param string $password
     *
     * @throws \Exception
     */
    public function reset(string $token, string $password)
    {
        $this->validatePassword($password);
        $this->validateConfirmationToken($token);

        /** @var User $user */
        $user = $this->getEm()->getRepository(User::class)->findOneBy(['confirmation_token' => $token]);

        if (!$user) {
            throw new ConfirmationTokenNotFoundException();
        }

        if (time() > ($user->getLastActivityTimestamp() + getenv('CONFIRMATION_TOKEN_LIFETIME'))) {
            throw new ConfirmationTokenExpiredException();
        } else {
            $user->setLastActivity(new \DateTime());
        }

        $user->setConfirmationToken(null);
        $user->setPassword($this->getEncoder()->encodePassword($user, $password));

        $this->getEm()->flush();
    }

    /**
     * @param string $email
     * @param bool $isNew
     */
    private function validateEmail(string $email, $isNew = false)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new BadRequestException(['email' => 'invalid']);
        }

        if ($isNew && $this->getEm()->getRepository(User::class)->findOneBy(['email' => $email])) {
            throw new EmailAlreadyRegisteredException();
        }
    }

    /**
     * @param string $token
     */
    private function validateConfirmationToken(string $token)
    {
        $options = ['options' => ['regexp' => self::CONFIRMATION_TOKEN_PATTERN]];
        if (!filter_var($token, FILTER_VALIDATE_REGEXP, $options)) {
            throw new ConfirmationTokenNotFoundException();
        }
    }

    /**
     * @param string $password
     * @throws \Exception
     */
    private function validatePassword(string $password)
    {
        if (empty($password)) {
            throw new PasswordRequiredException();
        }

        $options = ['options' => ['regexp' => self::USER_PASSWORD_PATTERN]];
        if (!filter_var($password, FILTER_VALIDATE_REGEXP, $options)) {
            throw new BadRequestException(['password' => 'invalid']);
        }
    }

    /**
     * @param User $user
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function sendRegisterEmail(User $user)
    {
        $link = $this->getRequest()->getHttpHost() . '/password/reset/' . urlencode($user->getConfirmationToken());

        $message = (new \Swift_Message())
            ->setSubject('You are registered at ' . getenv('APP_NAME'))
            ->setFrom([getenv('EMAIL_FROM_ADDRESS') => getenv('EMAIL_FROM_NAME')])
            ->setTo($user->getEmail())
            ->setBody(
                $this->getTwig()->render('email/register.html.twig', [
                    'link' => $link
                ]), 'text/html'
            );

        $this->getMailer()->send($message);
    }

    /**
     * @param User $user
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function sendForgotEmail(User $user)
    {
        $link = $this->getRequest()->getHttpHost() . '/password/reset/' . urlencode($user->getConfirmationToken());

        $message = (new \Swift_Message())
            ->setSubject('Link for reset password at ' . getenv('APP_NAME'))
            ->setFrom([getenv('EMAIL_FROM_ADDRESS') => getenv('EMAIL_FROM_NAME')])
            ->setTo($user->getEmail())
            ->setBody(
                $this->getTwig()->render('email/forgot.html.twig', [
                    'link' => $link
                ]), 'text/html'
            );

        $this->getMailer()->send($message);
    }

    /**
     * @param int $size
     *
     * @return string
     * @throws \Exception
     */
    private function createConfirmationToken(int $size = 32)
    {
        return bin2hex(random_bytes($size));
    }
}