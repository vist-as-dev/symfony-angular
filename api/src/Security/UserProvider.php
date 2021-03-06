<?php
namespace App\Security;

use App\Entity\User;
use App\Exception\Security\UserClassNotSupportedException;
use App\Exception\Security\UserNotFoundException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProvider implements UserProviderInterface
{
    private $_em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->_em = $em;
    }

    public function loadUserByUsername($id): ?User
    {
        /** @var User $user */
        try {
            $user = $this->_em->find(User::class, $id);
        } catch (\Exception $e) {
            $user = null;
        }

        if (!$user) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UserClassNotSupportedException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getId());
    }

    public function supportsClass($class)
    {
        return $class === User::class;
    }
}