<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Entity\Services;
use App\Form\ReservationType;
use App\Repository\PetRepository;
use App\Repository\ReservationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

#[Route('/reservation')]
final class ReservationController extends AbstractController
{
    private $entityManager;
    private $petRepository;

    // Inject EntityManagerInterface and PetRepository
    public function __construct(EntityManagerInterface $entityManager, PetRepository $petRepository)
    {
        $this->entityManager = $entityManager;
        $this->petRepository = $petRepository;
    }
    #[Route('/reservations',name: 'app_reservation_index', methods: ['GET'])]
    public function index(ReservationRepository $reservationRepository, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            throw new \Exception('User not authenticated');
        }


        // Fetch only the reservations for the currently authenticated user
        $reservations = $reservationRepository->findBy(['user' => $user]);

        return $this->render('reservation/index.html.twig', [
            'reservations' => $reservations,
            'user' => $user,
        ]);
    }

    #[Route('/reservationAdmin',name: 'app_reservation_index_admin', methods: ['GET'])]
    public function indexReservation_admin(ReservationRepository $reservationRepository, Security $security): Response
    {
        $user = $security->getUser();

        if (!$user) {
            throw new \Exception('User not authenticated');
        }


        // Fetch only the reservations for the currently authenticated user
        $reservations = $reservationRepository->findBy(['user' => $user]);

        return $this->render('admin/booking/booking.html.twig', [
            'reservations' => $reservations,
            'user' => $user,
        ]);
    }


    #[Route('/new/{serviceId}', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, FormFactoryInterface $formFactory, Security $security, int $serviceId): Response
    {
        $reservation = new Reservation();
        $user = $security->getUser();

        if (!$user) {
            throw new \Exception('User not authenticated');
        }

        $service = $this->entityManager->getRepository(Services::class)->find($serviceId);
        if (!$service) {
            throw $this->createNotFoundException('The service does not exist.');
        }

        // Set default status for the reservation
        $reservation->setStatus('not confirm');
        $reservation->setUser($user);

        $pets = $this->petRepository->findAll();

        $form = $this->createForm(ReservationType::class, $reservation, [
            'pets' => $pets,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $reservation->addService($service);
            $this->entityManager->persist($reservation);
            $this->entityManager->flush();

            return $this->redirectToRoute('app_reservation_index');
        }

        return $this->render('reservation/new.html.twig', [
            'reservation' => $reservation,
            'form' => $form->createView(),
            'user' => $user,
            'service' => $service,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_reservation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ReservationType::class, $reservation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('reservation/edit.html.twig', [
            'reservation' => $reservation,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_reservation_delete', methods: ['POST'])]
    public function delete(Request $request, Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reservation->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($reservation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_reservation_index', [], Response::HTTP_SEE_OTHER);
    }
}
