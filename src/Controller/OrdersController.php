<?php

namespace App\Controller;

use App\Entity\Orders;
use App\Entity\Shop;
use App\Form\OrdersType;
use App\Repository\OrdersRepository;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;


final class OrdersController extends AbstractController
{
    #[Route('/orders', name: 'app_orders_index', methods: ['GET'])]
    public function index(OrdersRepository $ordersRepository, Security $security): Response
    {
        // Get the logged-in user
        $user = $security->getUser();

        // Ensure the user is logged in
        if (!$user) {
            throw $this->createAccessDeniedException('You must be logged in to view orders.');
        }

        // Fetch confirmed and unconfirmed orders for the logged-in user
        $confirmedOrders = $ordersRepository->findBy(['user' => $user, 'status' => 'Confirmed']);
        $unconfirmedOrders = $ordersRepository->findBy(['user' => $user, 'status' => 'not confirmed']);

        return $this->render('orders/index.html.twig', [
            'confirmedOrders' => $confirmedOrders,
            'unconfirmedOrders' => $unconfirmedOrders,
            'user' => $user,
        ]);
    }


    #[Route('/ordersAdmin', name: 'app_orders_index_admin', methods: ['GET', 'POST'])]
    public function indexOrderAdmin(
        OrdersRepository $ordersRepository,
        Security $security,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $user = $security->getUser();

        // Check if a status update was requested
        $orderId = $request->query->get('updateStatus');
        if ($orderId) {
            $order = $ordersRepository->find($orderId);
            if ($order && $order->getStatus() !== 'Confirmed') {
                $order->setStatus('Confirmed');
                $em->persist($order);
                $em->flush();
            }

            // Optionally, you can redirect back to avoid resubmitting the form on refresh
            return $this->redirectToRoute('app_orders_index_admin');
        }

        // Fetch confirmed and unconfirmed orders
        $confirmedOrders = $ordersRepository->findBy(['status' => 'Confirmed']);
        $unconfirmedOrders = $ordersRepository->findBy(['status' => 'Not Confirmed']);

        // Render the orders list
        return $this->render('admin/orders/orders.html.twig', [
            'confirmedOrders' => $confirmedOrders,
            'unconfirmedOrders' => $unconfirmedOrders,
            'user' => $user,
        ]);
    }



    #[Route('/new', name: 'orders_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, Security $security,ShopRepository $shopRepository): Response
    {
        $user = $security->getUser(); // Get the logged-in user

        // Parse JSON data from the request (if POST request)
        if ($request->isMethod('POST')) {
            $data = json_decode($request->getContent(), true);

            if (!$data || !isset($data['price'], $data['status'], $data['dateOrder'], $data['shops'])) {
                return new JsonResponse(['success' => false, 'message' => 'Invalid data format!'], 400);
            }

            // Create a new order
            $order = new Orders();
            $order->setUser($user);
            $order->setPrice($data['price']);
            $order->setStatus($data['status']);
            $order->setDateOrder(new \DateTime($data['dateOrder']));
            $order->setQuantity(0); // Initialize quantity (will be updated later)

            // Persist the order
            $entityManager->persist($order);

            // Process shops and quantities
            $totalQuantity = 0;
            foreach ($data['shops'] as $shopData) {
                $shop = $entityManager->getRepository(Shop::class)->find($shopData['id']);
                if (!$shop) {
                    return new JsonResponse(['success' => false, 'message' => 'Shop not found!'], 404);
                }

                // Link shop to order with quantity
                $order->addShop($shop, $shopData['quantity']); // Assuming `addShop` handles pivot table logic
                $totalQuantity += $shopData['quantity'];
            }

            // Update total quantity in the Orders table
            $order->setQuantity($totalQuantity);

            // Save changes
            $entityManager->flush();

            // Return success as JSON and handle redirection on the client-side
            return new JsonResponse(['success' => true, 'redirect_url' => $this->generateUrl('app_orders_index')]);
        }

        // Render the form for new orders (GET request)
        return $this->render('shop/index.html.twig', [
            'user' => $user,
            'shops' => $shopRepository->findAll(),
        ]);
    }

#[Route('/orders/{id}', name: 'app_orders_show', methods: ['GET'])]
    public function show(Orders $order): Response
    {
        return $this->render('orders/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_orders_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Orders $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrdersType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_orders_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('orders/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/orders/{id}', name: 'app_orders_delete', methods: ['POST'])]
    public function delete(Request $request, Orders $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_orders_index_admin', [], Response::HTTP_SEE_OTHER);
    }
}
