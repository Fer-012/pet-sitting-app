<?php

namespace App\Controller;

use App\Repository\ReservationRepository;
use App\Repository\ServicesRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;

class DashboardController extends AbstractController
{
    #[Route('/index', name: 'app_dashboard', methods: ['GET'])]
    public function index(UserRepository $userRepository,Security $security): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('User not logged in.');
        }
        $totalClients = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles LIKE :role')
            ->setParameter('role', '%"ROLE_CLIENT"%') // Adjusted for the role string to match JSON format
            ->getQuery()
            ->getSingleScalarResult();

        return $this->render('admin/index.html.twig', [
            'controller_name' => 'DashboardController',
            'totalClients' => (int) $totalClients,
            'user' => $user,
        ]);
    }

    #[Route('/adminIndexS', name: 'app_services_admin_index', methods: ['GET'])]
    public function adminIndex(ServicesRepository $servicesRepository): Response
    {
        return $this->render('services/adminIndex.html.twig', [
            'services' => $servicesRepository->findAll(),
        ]);
    }

    #[Route('/indexRes', name: 'app_reservation_index', methods: ['GET'])]
    public function adminIndexRes(ReservationRepository $reservationRepository): Response
    {
        return $this->render('reservation/reservationList.html.twig', [
            'reservation' => $reservationRepository->findAll(),
        ]);
    }

    #[Route('/countInscriptions', name: 'app_count_inscriptions', methods: ['GET'])]
    public function countInscriptions(UserRepository $userRepository): Response
    {

        $totalClients = $userRepository->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('JSON_CONTAINS(u.roles, :role) = 1')
            ->setParameter('role', '"ROLE_CLIENT"')
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json([
            'totalClients' => (int) $totalClients,
        ]);
    }

    #[Route('/countReservations', name: 'app_count_reservations', methods: ['GET'])]
    public function countReservations(ReservationRepository $reservationRepository): Response
    {

        $totalReservations = $reservationRepository->count([]);

        return $this->json([
            'totalReservations' => $totalReservations,
        ]);
    }



}
