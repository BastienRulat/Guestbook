<?php

namespace App\Controller;

use App\Entity\Conference;
use App\Entity\Comment;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use Twig\Environment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ConferenceController extends AbstractController
{
    /**
     * @Route("/", name="homepage")
     */
    public function index(Environment $twig, ConferenceRepository $conferenceRepository): Response
    {
        // TODO Manage render exceptions
        return new Response($twig->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll()
        ]));
    }

    /**
     * @Route("/conference/{id}/{offset?}", name="conference")
     */
    public function conference(Request $request, Environment $twig, Conference $conference, CommentRepository $commentRepository)
    {
        // TODO Fix ERROR if id is not found
        $offset = max (0, $request->attributes->getInt('offset', 0));

        $paginator = $commentRepository->getCommentPaginator($conference, $offset);
        // dd($paginator);
        return new Response($twig->render('conference/conference.html.twig', [

            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - $commentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + $commentRepository::PAGINATOR_PER_PAGE)
        ]));
    }
}