<?php

namespace App\Controller;

use App\Repository\AffordablePackageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

class ClientController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(AffordablePackageRepository $affordablePackageRepository,Security $security): Response
    {
        $user = $security->getUser();
        return $this->render('client/dashboard.html.twig', [
            'controller_name' => 'ClientController',
            'user' => $user,
            'affordable_packages' => $affordablePackageRepository->findAll(),
        ]);
    }

    #[Route('/app', name: 'app_app')]
    public function indexClient(): Response
    {
        return $this->render('client/index.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function home(): Response
    {
        return $this->render('client/about.html.twig');
    }

    #[Route('/vet', name: 'app_vet')]
    public function vet(): Response
    {
        return $this->render('client/vet.html.twig');
    }

    #[Route('/services', name: 'app_services')]
    public function services(): Response
    {
        return $this->render('client/services.html.twig');
    }

    #[Route('/gallery', name: 'app_gallery')]
    public function gallery(): Response
    {
        return $this->render('client/gallery.html.twig');
    }

    #[Route('/pricing', name: 'app_pricing')]
    public function pricing(): Response
    {
        return $this->render('client/pricing.html.twig');
    }

    #[Route('/blog', name: 'app_blog')]
    public function blog(): Response
    {
        return $this->render('client/blog.html.twig');
    }

    #[Route('/contact', name: 'app_contact')]
    public function contact(): Response
    {
        return $this->render('client/contact.html.twig');
    }
}