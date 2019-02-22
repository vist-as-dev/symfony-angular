<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Lcobucci\JWT\Token;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityService extends AbstractService
{
    use
        TraitEncoder,
        TraitMailer,
        TraitTwig,
        TraitLogger;

    /**
     * AuthService constructor.
     * @param LoggerInterface $logger
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $encoder
     * @param \Swift_Mailer $mailer
     * @param \Twig_Environment $twig
     */
    public function __construct(LoggerInterface $logger,
                                EntityManagerInterface $em,
                                UserPasswordEncoderInterface $encoder,
                                \Swift_Mailer $mailer,
                                \Twig_Environment $twig)
    {
        $this->setEm($em);
        $this->setLogger($logger);
        $this->setEncoder($encoder);
        $this->setMailer($mailer);
        $this->setTwig($twig);
    }

    /**
     * @param Environment $environment
     * @param array $data
     * @throws \ErrorException
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function signup(Environment $environment, array $data)
    {
        $this->signupValidate($data);

        if ($this->getEm()->getRepository(User::class)
            ->findOneBy(['email' => $data['email'], 'environment' => $environment])
        ) {
            throw new \ErrorException('User already registered');
        }

        $user = new User();
        $user->setUsername($data['username'] ?? '');
        $user->setEmail($data['email']);
        $user->setPassword($this->getEncoder()->encodePassword($user, $data['password']));
        $user->setStatus(UserStatus::SUSPENDED);
        $user->addRole(IUserRoles::AFFILIATE);
        $user->setEnvironment($environment);
        $user->setConfirmationToken($token = $this->getEncoder()->encodePassword($user, $data['email']));
        /** @var User $manager */
        if (!empty($data['manager'])
            && $manager = $this->getEm()->find(User::class, (int)$data['manager'])
        ) {
            if ($manager->isRoles(IUserRoles::MANAGEMENT_ARRAY)) {
                $user->setManager($manager);
            }
        }
        /** @var User $master */
        if (!empty($data['master'])
            && $master = $this->getEm()->find(User::class, (int)$data['master'])
        ) {
            $user->setMasterAffiliate($master);
        }
        $this->getEm()->persist($user);
        $this->getEm()->flush();

        if (!empty($data['details'])) {
            /** @var Collection $metas */
            $details = $this->getEm()
                ->getRepository(UserDetailMeta::class)
                ->matching(
                    Criteria::create()->where(
                        Criteria::expr()->in('alias', array_keys($data['details']))
                    )
                );
            /** @var UserDetailMeta $meta */
            foreach ($details as $meta) {
                if (!empty($data['details'][$meta->getAlias()])) {
                    $this->getEm()->persist(new UserDetailData($user, $meta, $data['details'][$meta->getAlias()]));
                }
            }
            $this->getEm()->flush();
        }
        $this->sendSignUpEmail($environment, $user);
    }

    /**
     * @param Environment $environment
     * @param string $token
     */
    public function signupConfirm(Environment $environment, string $token)
    {
        /** @var User $user */
        if (!$user = $this->getEm()->getRepository(User::class)
            ->findOneBy(['confirmation_token' => $token, 'environment' => $environment])
        ) {
            throw new NotFoundHttpException('User not found');
        }
        $user->setStatus(UserStatus::PENDING);
        $user->setConfirmationToken(null);
        $this->getEm()->flush();
    }

    /**
     * @param Environment $environment
     * @param array $data
     * @return Token
     * @throws \Exception
     */
    public function login(Environment $environment, array $data)
    {
        $this->loginValidate($data);

        /** @var User $user */
        if (!$user = $this->getEm()->getRepository(User::class)->findOneBy([
            'email' => $data['email'],
            'environment' => $environment
        ])
        ) {
            throw new NotFoundHttpException('User not found');
        }
        if (!$this->getEncoder()->isPasswordValid($user, $data['password'] ?? '')) {
            throw new AccessDeniedHttpException('Password incorrect');
        }
        if (!$user->isStatus(UserStatus::APPROVED)) {
            throw new AccessDeniedHttpException('You are not approved yet.');
        }

        if (!$last_login = $user->getLastLogin()) {
            $last_login = new UserLastLogin($user);
        }
        $last_login->setLastLogin(time());
        $user->setLastLogin($last_login);
        $this->getEm()->flush();

        $secret_key = 'secret';
        $signer = new Sha256();

        $role = $user->isRoles(IUserRoles::AFFILIATE) ? 1 : 0;
        $role += $user->isRoles(IUserRoles::MANAGEMENT) ? 1 : 0;
        $role += $user->isRoles(IUserRoles::ADMINS) ? 1 : 0;

        return (new Builder())
            ->setIssuer($environment->getDomain())
            ->setAudience($environment->getDomain())
            ->setId($user->getId(), true)
            ->setIssuedAt(time())
            ->setNotBefore(time() + 0)
            ->setExpiration(time() + 3600)
            ->set('rol', $role)
            ->sign($signer, $secret_key)
            ->getToken();
    }

    /**
     * @param Environment $environment
     * @param array $data
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function forgot(Environment $environment, array $data)
    {
        $this->forgotValidate($data);
        /** @var User $user */
        if (!$user = $this->getEm()->getRepository(User::class)->findOneBy([
            'email' => $data['email'],
            'environment' => $environment
        ])
        ) {
            throw new NotFoundHttpException('Email not found');
        }
        $user->setConfirmationToken($token = $this->getEncoder()->encodePassword($user, $data['email']));
        $user->setTimePasswordResetRequested(time());
        $this->getEm()->flush();
        $this->sendForgotEmail($environment, $user);
    }

    /**
     * @param Environment $environment
     * @param string $token
     * @param array $data
     * @throws \Exception
     */
    public function reset(Environment $environment, string $token, array $data)
    {
        $this->resetValidate($data);
        /** @var User $user */
        if (!$user = $this->getEm()->getRepository(User::class)
            ->findOneBy(['confirmation_token' => $token, 'environment' => $environment])
        ) {
            throw new NotFoundHttpException('User not found or token has been used. Try again.');
        }
        if ((time() - (int)$user->getTimePasswordResetRequested()) > 60 * 60 * 1000) {
            throw new AccessDeniedHttpException('Token expired');
        }
        $user->setConfirmationToken(null);
        $user->setTimePasswordResetRequested(null);
        $user->setPassword($this->getEncoder()->encodePassword($user, $data['password']));
        $this->getEm()->flush();
    }

    /**
     * @param Environment $environment
     * @param array $data
     * @throws \ErrorException
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    public function createEnvironment(Environment $environment, array $data)
    {
        $this->createEnvironmentValidate($data);
        if (!$domain = parse_url($data['domain'], PHP_URL_HOST)) {
            $domain = parse_url($data['domain'], PHP_URL_PATH);
        }
        if ($this->getEm()->getRepository(Environment::class)->findOneBy(['domain' => $domain])) {
            throw new \ErrorException('Domain "' . $domain . '" is used already.');
        }

        $env = new Environment($data['domain']);
        $env->setCompany($data['company'] ?? null);
        $env->setIsEnabled(false);
        $this->getEm()->persist($env);
        $this->getEm()->flush();

        $user = new User();
        $user->setUsername($data['username'] ?? null);
        $user->setEmail($data['email']);
        $user->setPassword($this->getEncoder()->encodePassword($user, $data['password']));
        $user->setStatus(UserStatus::SUSPENDED);
        $user->addRole(IUserRoles::SUPER_ADMIN);
        $user->setEnvironment($env);
        $user->setConfirmationToken($token = $this->getEncoder()->encodePassword($user, $data['email']));
        $this->getEm()->persist($user);
        $this->getEm()->flush();

        $this->sendCreateEnvironmentEmail($environment, $user);
    }

    /**
     * @param string $token
     */
    public function confirmCreateEnvironment(string $token)
    {
        /** @var User $user */
        if (!$user = $this->getEm()->getRepository(User::class)->findOneBy([
            'confirmation_token' => $token,
        ])
        ) {
            throw new AccessDeniedHttpException('Token incorrect');
        }
        $user->setStatus(UserStatus::PENDING);
        $user->setConfirmationToken(null);
        $user->getEnvironment()->setIsEnabled(true);
        $this->getEm()->flush();
    }

    /**
     * @param string $host
     * @return Environment
     */
    public function getEnvironment(string $host)
    {
        /** @var Environment $environment */
        $environment = $this->getEm()
            ->getRepository(Environment::class)
            ->findOneBy(['domain' => $host]);
        if (!$environment) {
            throw new NotFoundHttpException('Domain not found');
        }
        if (!$environment->isEnabled()) {
            throw new AccessDeniedHttpException('Domain is blocked');
        }
        return $environment;
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    private function signupValidate(array $data)
    {
        if (empty($data['email'])
            || empty(trim($data['email']))
            || empty($data['password'])
            || empty(trim($data['password']))
        ) {
            throw new BadRequestHttpException('Request parameters are incorrect');
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    private function loginValidate(array $data)
    {
        if (empty($data['email'])
            || empty(trim($data['email']))
            || empty($data['password'])
            || empty(trim($data['password']))
        ) {
            throw new BadRequestHttpException('Request parameters are incorrect');
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    private function forgotValidate(array $data)
    {
        if (empty($data['email'])
            || empty(trim($data['email']))
        ) {
            throw new BadRequestHttpException('Request parameters are incorrect');
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    private function resetValidate(array $data)
    {
        if (empty($data['password'])
            || empty(trim($data['password']))
        ) {
            throw new BadRequestHttpException('Request parameters are incorrect');
        }
    }

    /**
     * @param array $data
     * @throws \Exception
     */
    private function createEnvironmentValidate(array $data)
    {
        if (
            empty($data['email']) || empty(trim($data['email']))
            || empty($data['password']) || empty(trim($data['password']))
            || empty($data['domain']) || empty(trim($data['domain']))
        ) {
            throw new BadRequestHttpException('Request parameters are incorrect');
        }
    }

    /**
     * @param Environment $environment
     * @param User $user
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function sendSignUpEmail(Environment $environment, User $user)
    {
        $link = 'http://' . $environment->getDomain() . '/signup/confirm/' . urlencode($user->getConfirmationToken());
        $message = (new \Swift_Message())
            ->setSubject('You are registered at ' . $environment->getCompany())
            ->setFrom(['gaa.notificator@shinningcreation.com' => 'Administration'])
            ->setTo([$user->getEmail() => $user->getUsername()])
            ->setBody(
                $this->getTwig()->render('email/signup.twig', [
                    'user' => $user,
                    'confirmationUrl' => $link
                ]), 'text/html'
            );
        if (!$this->getMailer()->send($message)) {
            throw new \Exception('Confirmation email has not been sent.');
        }
    }

    /**
     * @param Environment $environment
     * @param User $user
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function sendForgotEmail(Environment $environment, User $user)
    {
        $link = 'http://' . $environment->getDomain() . '/reset/' . urlencode($user->getConfirmationToken());
        $message = (new \Swift_Message())
            ->setSubject('Link for reset password at ' . $environment->getCompany())
            ->setFrom(['gaa.notificator@shinningcreation.com' => 'Administration'])
            ->setTo([$user->getEmail() => $user->getUsername()])
            ->setBody(
                $this->getTwig()->render('email/forgot.twig', [
                    'user' => $user,
                    'resetUrl' => $link
                ]), 'text/html'
            );
        if (!$this->getMailer()->send($message)) {
            throw new \Exception('Reset password email has not been sent.');
        }
    }

    /**
     * @param Environment $environment
     * @param User $user
     * @throws \Exception
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     */
    private function sendCreateEnvironmentEmail(Environment $environment, User $user)
    {
        $link = 'http://' . $environment->getDomain() . '/owner/confirm/' . urlencode($user->getConfirmationToken());
        $message = (new \Swift_Message())
            ->setSubject('You created the own environment at ' . $environment->getCompany())
            ->setFrom(['gaa.notificator@shinningcreation.com' => 'Administration'])
            ->setTo([$user->getEmail() => $user->getUsername()])
            ->setBody(
                $this->getTwig()->render('email/create.twig', [
                    'user' => $user,
                    'confirmationUrl' => $link
                ]), 'text/html'
            );
        if (!$this->getMailer()->send($message)) {
            throw new \Exception('Confirmation email has not been sent.');
        }
    }
}