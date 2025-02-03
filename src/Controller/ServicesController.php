<?php

namespace App\Controller;

use App\Entity\Services;
use App\Form\ServicesType;
use App\Repository\ServicesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;


final class ServicesController extends AbstractController
{
    #[Route('/indexS',name: 'app_services_index', methods: ['GET'])]
    public function index(ServicesRepository $servicesRepository,Security $security): Response
    {
        $user = $security->getUser();
        return $this->render('services/index.html.twig', [
            'services' => $servicesRepository->findAll(),
            'user' => $user,
        ]);
    }

    #[Route('/indexService', name: 'app_services_index_admin', methods: ['GET', 'POST'])]
    public function indexServiceAdmin(
        ServicesRepository $servicesRepository,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Fetch all services
        $services = $servicesRepository->findAll();

        // Create a new service form for adding a new service
        $service = new Services();
        $form = $this->createForm(ServicesType::class, $service);
        $form->handleRequest($request);

        // Handle form submission for new service
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($service);
            $entityManager->flush();

            return $this->redirectToRoute('app_services_index_admin', [], Response::HTTP_SEE_OTHER);
        }

        // Handle form submission for editing an existing service
        if ($request->query->get('edit')) {
            // Find service by ID for editing
            $editServiceId = $request->query->get('edit');
            $serviceToEdit = $servicesRepository->find($editServiceId);

            if ($serviceToEdit) {
                // Create form for editing the service
                $form = $this->createForm(ServicesType::class, $serviceToEdit);
                $form->handleRequest($request);

                // If form is submitted and valid, update the service
                if ($form->isSubmitted() && $form->isValid()) {
                    $entityManager->flush(); // Save the updated service
                    return $this->redirectToRoute('app_services_index_admin', [], Response::HTTP_SEE_OTHER);
                }
            }
        }

        // Render the template
        return $this->render('admin/services/services.html.twig', [
            'services' => $services,
            'form' => $form->createView(),
        ]);
    }



    #[Route('/services/{id}', name: 'app_services_show', methods: ['GET'])]
    public function show(Services $services): Response
    {
        return $this->render('admin/services/show.html.twig', [
            'service' => $services,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_services_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Services $service, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ServicesType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_services_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/services/edit.html.twig', [
            'service' => $service,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_services_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, ServicesRepository $servicesRepository, EntityManagerInterface $entityManager): Response
    {
        $service = $servicesRepository->find($id);

        if (!$service) {
            throw $this->createNotFoundException('Service not found.');
        }

        if ($this->isCsrfTokenValid('delete' . $service->getId(), $request->request->get('_token'))) {
            $entityManager->remove($service);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_services_index_admin', [], Response::HTTP_SEE_OTHER);
    }


}
