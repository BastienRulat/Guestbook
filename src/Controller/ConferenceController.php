<?php

namespace App\Controller;

use Twig\Environment;
use App\Entity\Comment;
use App\Entity\Conference;
use App\Form\CommentFormType;
use App\Repository\CommentRepository;
use App\Repository\ConferenceRepository;
use App\SpamChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ConferenceController extends AbstractController
{
    private $twig;
    private $entityManager;

    public function __construct(Environment $twig, EntityManagerInterface $entityManager)
    {
        $this->twig = $twig;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/", name="homepage")
     */
    public function index(ConferenceRepository $conferenceRepository) : Response
    {
        // TODO Manage render exceptions
        return new Response($this->twig->render('conference/index.html.twig', [
            'conferences' => $conferenceRepository->findAll()
        ]));
    }

    /**
     * @Route("/conference/{slug}/{offset?}", name="conference")
     */
    public function conference(Request $request, Conference $conference, CommentRepository $commentRepository, string $photoDir, SpamChecker $spamChecker) : Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentFormType::class, $comment);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            
            $comment->setConference($conference);

            if ($photo = $form['photo']->getData()) {
                $filename = bin2hex(random_bytes(6)).'.'.$photo->guessExtension();
                try {
                    $photo->move($photoDir, $filename);
                } catch (FileException $e) {
                    // Unable to upload the photo, give up
                }
                $comment->setPhotoFilename($filename);
            }

            $this->entityManager->persist($comment);
            
            // BEGIN CHECK COMMENT IS SPAM WITH AKISMET API
            $context = [
                'user_ip' => $request->getClientIp(),
                'user_agent' => $request->headers->get('user-agent'),
                'referrer' => $request->headers->get('referer'),
                'permalink' => $request->getUri(),
            ];
            dd($spamChecker->getSpamScore($comment, $context));
            if (2 === $spamChecker->getSpamScore($comment, $context)) {
                throw new \RuntimeException('Blatant spam, go away!');
            }
            // END CHECK COMMENT IS SPAM WITH AKISMET API

            $this->entityManager->flush();

            return $this->redirectToRoute('conference', ['slug' => $conference->getSlug()]);
        }

        // TODO Fix ERROR if id is not found
        $offset = max (0, $request->attributes->getInt('offset', 0));

        $paginator = $commentRepository->getCommentPaginator($conference, $offset);
        // dd($paginator);
        return new Response($this->twig->render('conference/conference.html.twig', [

            'conference' => $conference,
            'comments' => $paginator,
            'previous' => $offset - $commentRepository::PAGINATOR_PER_PAGE,
            'next' => min(count($paginator), $offset + $commentRepository::PAGINATOR_PER_PAGE),
            'comment_form' => $form->createView()
        ]));
    }
}