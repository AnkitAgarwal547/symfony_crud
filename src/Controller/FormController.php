<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Post;
use App\Form\PostType;

class FormController extends AbstractController
{
    /**
     * @Route("/form", name="form")
     */
    public function index(): Response
    {
    	$post = new Post();

    	$form = $this->createForm(PostType::class, $post);

    	return $this->render('form/index.html.twig', [
            'post_form' => $form->createView()
        ]);
    }
}
