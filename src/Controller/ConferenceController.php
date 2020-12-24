<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConferenceController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(Request $request): Response
    {
        /* Initial maker default code 
        return $this->render('conference/index.html.twig', [
            'controller_name' => 'ConferenceController',
        ]);
        */

        $greet = null;
        if ($name = $request->query->get("hello"))
            $greet = "Hello " . htmlspecialchars($name);
       
        var_dump($name);
    
        return new Response ('
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Homepage</title>
            </head>
            <body>
                ' . $greet . '
            </body>
            </html>
        ');
    }
}