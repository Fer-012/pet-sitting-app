<?php

namespace App\Controller;

use App\Entity\Pet;
use App\Form\PetType;
use App\Repository\PetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/pet')]
final class PetController extends AbstractController
{
    #[Route(name: 'app_pet_index', methods: ['GET'])]
    public function index(PetRepository $petRepository,Security $security): Response
    {
        $user = $security->getUser();
        $userPets = $petRepository->findBy(['user' => $user]);

        return $this->render('pet/index.html.twig', [
            'pets' => $userPets,
            'user' => $user,
        ]);
    }

    #[Route('/new', name: 'app_pet_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger,Security $security): Response
    {
        $user = $security->getUser();
        $pet = new Pet();
        $pet->setUser($user);
        $form = $this->createForm(PetType::class, $pet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $photoFile->guessExtension();

                try {
                    $photoFile->move(
                        $this->getParameter('pets_directory'),
                        $newFilename
                    );
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Photo upload failed!');
                }

                $pet->setPhoto($newFilename);
            }

            $entityManager->persist($pet);
            $entityManager->flush();

            return $this->redirectToRoute('app_pet_index');
        }

        return $this->render('pet/new.html.twig', [
            'pet' => $pet,
            'form' => $form,
            'user' => $user,
        ]);
    }


    #[Route('/{id}', name: 'app_pet_show', methods: ['GET'])]
    public function show(Pet $pet): Response
    {
        return $this->render('pet/show.html.twig', [
            'pet' => $pet,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_pet_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Pet $pet, EntityManagerInterface $entityManager,Security $security): Response
    {
        $user = $security->getUser();
        $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/pets';

        $form = $this->createForm(PetType::class, $pet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photoFile = $form->get('photo')->getData();

            if ($photoFile) {
                // Generate a unique filename for the uploaded photo
                $originalFilename = pathinfo($photoFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = uniqid($originalFilename);
                $newFilename = $safeFilename . '.' . $photoFile->guessExtension();

                try {
                    // Move the uploaded file to the specified directory
                    $photoFile->move($uploadDir, $newFilename);

                    // Set the new filename to the 'photo' property
                    $pet->setPhoto($newFilename);

                } catch (\Exception $e) {
                    $this->addFlash('error', 'Photo upload failed!');
                }
            }

            $entityManager->persist($pet);
            $entityManager->flush();

            return $this->redirectToRoute('app_pet_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('client/addPet.html.twig', [
            'pet' => $pet,
            'form' => $form,
            'user' => $user,
        ]);
    }



    #[Route('/{id}', name: 'app_pet_delete', methods: ['POST'])]
    public function delete(Request $request, Pet $pet, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$pet->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($pet);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_pet_index', [], Response::HTTP_SEE_OTHER);
    }

}
