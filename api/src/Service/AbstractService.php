<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractService
{
    private $_em;

    /**
     * @return EntityManagerInterface|null
     */
    public function getEm(): ?EntityManagerInterface
    {
        return $this->_em;
    }

    /**
     * @param EntityManagerInterface $em
     *
     * @return AbstractService
     */
    public function setEm(EntityManagerInterface $em): self
    {
        $this->_em = $em;

        return $this;
    }
}