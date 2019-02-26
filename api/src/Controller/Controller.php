<?php

namespace App\Controller;


use App\Exception\Request\BadRequestException;
use Symfony\Component\HttpFoundation\Request;

class Controller extends \Symfony\Bundle\FrameworkBundle\Controller\Controller
{
    protected function getJsonContent(Request $request)
    {
        $data = [];

        try {
            if ($content = $request->getContent()) {
                $data = json_decode($content, true);
            }
        } catch (\Exception $e) {
            throw new BadRequestException();
        }

        return $data;
    }
}