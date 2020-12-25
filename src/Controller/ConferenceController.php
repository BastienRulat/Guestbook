<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use PhpParser\Node\Expr\Array_ as ExprArray_;
use PhpParser\Node\Expr\Cast\Array_;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConferenceController extends AbstractController
{
    /**
     * @Route("/", name="list-conference")
     */
    public function index(Environment $twig, ConferenceRepository $conferenceRepository): Response
    {
        // TODO Manage render exceptions
        return new Response($twig->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll()
        ]));
    }

    /**
     * @Route("/show/{id}", name="show-conference")
     */
    public function show(Environment $twig, Conference $conference, CommentRepository $commentRepository, $id)
    {
        return new Response($twig->render('conference/show.html.twig', [
            'conference' => $conference,
            'comments' => $commentRepository->findBy(["conference"=>$conference])
        ]));
    }
}