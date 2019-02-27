<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class TopicController
 * @package App\Controller
 *
 * @Route("/topic")
 */
class TopicController
{
    /**
     * @Route("")
     *
     * @return JsonResponse
     */
    public function listAction()
    {
        return new JsonResponse(['status' => 'success']);
    }
}