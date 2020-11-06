<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Post;

class PostController extends AbstractController
{
    /**
     * @Route("/post", name="post")
     */
    public function index(): Response
    {
        if (!$this->getUser()) {
            return $this->redirectToRoute('login');
        }
        $repository = $this->getDoctrine()->getRepository(Post::class);
    	$posts = $repository->findAll();
        
        return $this->render('post/index.html.twig', [
            'posts' => $posts,
        ]);
    }
    /**
     * @Route("/post/create", name="post_create", methods={"GET","POST"})
     */
    public function createPost(Request $request): Response
    {
        if ($request->isMethod('get')) {
            return $this->render('post/create.html.twig', ['error' => false, 'message' => '']);
        }
        // Get Request Token
        $token = $request->get("token");
        // Match CSRF Token
        if (!$this->isCsrfTokenValid('upload', $token)){
            $logger->info("CSRF failure");

            return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
        }
        $file = $request->files->get('media');
        if (empty($file)){
            return new Response("No file specified",
               Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }
        $directory = $this->getParameter('upload_directory'); 
        try {
            $filename = $file->getClientOriginalName();
            $post = new Post();
            // cREATE tIME
            $var = date_create();
            $time = date_format($var, 'dHis');
            $fileTime = $time;
            $filename = $fileTime.'-'.$filename;
            // Move File to directory
            $file->move( $directory, $filename);
            
            // $time = (int)$time;
            // return $this->json(['data' => $request->request->all()]);
            $post->setTitle($request->get('title'));
            $post->setDescription($request->get('desc'));
            $post->setMedia($filename);
            // new \DateTime()
            $post->setCreated($request->get('calender'));
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
        } catch (\Exception $e) {
            // $this->getRequest()->getSession()->setFlash('notice', "your_message"); 
            return $this->render('post/create.html.twig', ['error' => true, 'message' => $e->getMessage()]);
        }
        return $this->render('post/create.html.twig', ['error' => false, 'message' => 'Create Successfully!']);
    }
    /**
     * @Route("/post/{id<\d+>}/edit", methods="GET|POST", name="admin_post_edit")
     */
    public function editPost(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $post = $em->getRepository(Post::class)->find($request->get('id'));
        $directory = $this->getParameter('upload_directory'); 
        if ($request->isMethod('get')) {
            return $this->render('post/edit.html.twig', ['post' => $post, 'error' => false, 'message' => '']);
        }
        if (!$post) {
            throw $this->createNotFoundException(
                'No Post found for id '.$id
            );
        }
        $file = $request->files->get('media');
        if ($file) {
            $filename = $file->getClientOriginalName();
            $var = date_create();
            $time = date_format($var, 'dHis');
            $fileTime = $time;
            $filename = $fileTime.'-'.$filename;
            // Move File to directory
            $file->move( $directory, $filename);
        }
        $post->setTitle($request->get('title'));
        $post->setDescription($request->get('desc'));
        if($file){
            $post->setMedia($filename);
        }
        $post->setCreated($request->get('calender'));

        $em->flush();
        return $this->redirectToRoute('admin_post_edit', [
            'id' => $post->getId()
        ]);
    }
    /**
     * @Route("/post/{id<\d+>}", methods="GET|POST", name="admin_post_delete")
     */
    public function deletePost(Request $request): Response
    {
        $em = $this->getDoctrine()->getManager();
        $posts = $em->getRepository(Post::class)->findOneBy(['id' => $request->get('id')]);
        if($posts) {
            // Remove post
            $em->remove($posts);
        }
        $em->flush();
        // $this->getRequest()->getSession()->setFlash('message', "Deleted Successfully!"); 
        // $this->headers()->addsession->set('message', 'Deleted Successfully!');
        
        return $this->redirectToRoute('post');
    }
}
