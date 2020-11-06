<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Post;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(Request $request): Response
    {
        $repository = $this->getDoctrine()->getRepository(Post::class);
        $posts = $repository->findAll();
        return $this->render('home/index.html.twig', [
            'posts' => $posts,
        ]);
    }
    /**
     * @Route("home/post/{id<\d+>}", methods="GET", name="user_post_view")
     */
    public function show(Request $request): Response 
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($request->get('id'));
        return $this->render('home/show.html.twig', ['post' => $post, 'error' => false, 'message' => '']);
    }
}
