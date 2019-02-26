<?php

namespace App\Controller\Auxiliary;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

class OPTIONSController extends Controller
{
    /**
     * @Route("/{url}", name="options_request_handler", methods="OPTIONS", requirements={"url"=".*"})
     */
    public function index()
    {
        return $this->json(['message' => 'No Content'], Response::HTTP_NO_CONTENT);
    }
}
