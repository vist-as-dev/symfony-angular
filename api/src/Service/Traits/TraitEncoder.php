<?php

namespace App\Service\Traits;


use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

trait TraitEncoder
{
    private $encoder;

    /**
     * @return UserPasswordEncoderInterface
     */
    public function getEncoder()
    {
        return $this->encoder;
    }

    /**
     * @param UserPasswordEncoderInterface $encoder
     */
    public function setEncoder(UserPasswordEncoderInterface $encoder): void
    {
        $this->encoder = $encoder;
    }
}