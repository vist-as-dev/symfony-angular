<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

abstract class AbstractService
{
    /** @var EntityManagerInterface */
    private $_em;

    /**
     * @return EntityManagerInterface
     */
    public function getEm(): ?EntityManagerInterface
    {
        return $this->_em;
    }

    /**
     * @param EntityManagerInterface $em
     */
    public function setEm(?EntityManagerInterface $em): void
    {
        $this->_em = $em;
    }
}