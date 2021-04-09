<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\String\Slugger\SluggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\RedirectResponse;

use App\Repository\BlogRepository;
use App\Entity\Blog;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Form\BlogFormType;
use App\Form\RegistrationFormType;
use App\Repository\CategoryRepository;

class MainController extends AbstractController
{
    /**
     * @Route("/")
     *
     * @param BlogRepository $blogRepository
     * 
     * @param CategoryRepository $categoryRepository
     *
     * @return Response
     */
    public function index(Request $request, BlogRepository $blogRepository, CategoryRepository $categoryRepository)
    {
        $category = $request->query->has('category') ? $categoryRepository->find($request->query->get('category')) : null;

        if ($category)
            $blogs = $blogRepository->findBy([ 'category' => $category ]);
        else
            $blogs = $blogRepository->findAll();

        return $this->render('list.html.twig', [
            'blogs' => $blogs,
            'categories' => $categoryRepository->findAll(),
            'selectedCategory' => $category
        ]);
    }

     /**
     * @Route("/create")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function createBlog(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        $blog = new Blog();
        
        $form = $this->createForm(BlogFormType::class, $blog);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('imageFile')->getData();
            
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('image_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Image cannot be saved.');
                }
                $blog->setImage($newFilename);
            }

            $entityManager->persist($blog);
            $entityManager->flush();
            $this->addFlash('success', 'Blog was created!');
            return $this->redirectToRoute('app_main_index');
        }	
        return $this->render('create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/edit/{id}")
     *
     * @ParamConverter("blog", class="App:Blog")
     *
     * @return Response
     */
    public function editBlog(Blog $blog, Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger)
    {
        if (!$blog->getImage()){
            $blog->setImage(new File(sprintf('%s/%s', $this->getParameter('image_directory'), $blog->getImage())));
        }
        $form = $this->createForm(BlogFormType::class, $blog);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $blog      = $form->getData();
            $imageFile = $form->get('imageFile')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);

                $safeFilename = $slugger->slug($originalFilename);
                $newFilename  = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                            $this->getParameter('image_directory'),
                            $newFilename
                        );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Image cannot be saved.');
                }
                $blog->setImage($newFilename);
            }

            $entityManager->persist($blog);
            $entityManager->flush();
            $this->addFlash('success', 'Blog was edited!');
            return $this->redirectToRoute('app_main_index');
        }

        return $this->render('create.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/delete/{id}", name="app_blog_delete")
     *
     * @param Blog                   $blog
     * @param EntityManagerInterface $em
     *
     * @return RedirectResponse
     */
    public function deleteBlog(Blog $blog, EntityManagerInterface $em): RedirectResponse
    {
        $em->remove($blog);
        $em->flush();
        $this->addFlash('success', 'Blog was edited!');

        return $this->redirectToRoute('app_main_index');
    }

    /**
     * 
     * @Route("/read/{id}", name="app_blog_read")
     * 
     * @param Blog $blog
     * @param EntityManagerInterface $em
     * @param Request $request
     * @return RedirectResponse
     * 
     */

     public function readBlog(Blog $blog , Request $request, EntityManagerInterface $entityManager)
     {
         return $this->render('read.html.twig', [
            'read' => $blog
        ]);
         
     }

}