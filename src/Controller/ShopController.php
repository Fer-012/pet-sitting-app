<?php

namespace App\Controller;

use App\Entity\Shop;
use App\Form\ShopType;
use App\Repository\ShopRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ShopController extends AbstractController
{
    #[Route('/shop',name: 'app_shop_index', methods: ['GET'])]
    public function index(ShopRepository $shopRepository,Security $security): Response
    {
        $user = $security->getUser();
        return $this->render('shop/index.html.twig', [
            'shops' => $shopRepository->findAll(),
            'user' => $user,
        ]);
    }

    #[Route('/shopAdmin',name: 'app_shop_index_admin', methods: ['GET'])]
    public function indexShopAdmin(ShopRepository $shopRepository,Security $security): Response
    {
        $user = $security->getUser();
        return $this->render('admin/products/products.html.twig', [
            'shops' => $shopRepository->findAll(),
            'user' => $user,
        ]);
    }

    #[Route('/shop/new', name: 'app_shop_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        SluggerInterface $slugger,
        Security $security
    ): Response {
        $user = $security->getUser();
        $shop = new Shop();
        $form = $this->createForm(ShopType::class, $shop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $pictureFile = $form->get('picture')->getData();

            if ($pictureFile) {
                $originalFilename = pathinfo($pictureFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $pictureFile->guessExtension();

                try {
                    $pictureFile->move(
                        $this->getParameter('shops_directory'),
                        $newFilename
                    );
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Picture upload failed!');
                }

                $shop->setPicture($newFilename);
            }

            $entityManager->persist($shop);
            $entityManager->flush();

            return $this->redirectToRoute('app_shop_index_admin');
        }

        return $this->render('admin/products/newProduct.html.twig', [
            'shop' => $shop,
            'form' => $form,
            'user' => $user,
        ]);
    }


    #[Route('/shop/{id}', name: 'app_shop_show', methods: ['GET'])]
    public function show(Shop $shop): Response
    {
        return $this->render('shop/show.html.twig', [
            'shop' => $shop,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_shop_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Shop $shop, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ShopType::class, $shop);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_shop_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('shop/edit.html.twig', [
            'shop' => $shop,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_shop_delete', methods: ['POST'])]
    public function delete(Request $request, Shop $shop, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$shop->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($shop);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_shop_index', [], Response::HTTP_SEE_OTHER);
    }
}
