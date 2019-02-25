<?php

namespace App\Service\Traits;


use Symfony\Component\HttpFoundation\Request;

trait TraitRequest
{
    private $request;

    /**
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request): void
    {
        $this->request = $request;
    }
}