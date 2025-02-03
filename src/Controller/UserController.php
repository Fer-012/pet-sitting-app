<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\PetRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;


final class UserController extends AbstractController
{
    #[Route('/users', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository, PetRepository $petRepository): Response
    {
        // Retrieve all users
        $users = $userRepository->findAll();

        // Retrieve all pets
        $pets = $petRepository->findAll();

        // Filter users with the role "CLIENT_ROLE"
        $clients = array_filter($users, function ($user) {
            return in_array('ROLE_CLIENT', $user->getRoles(), true);
        });

        // Count the number of pets for each client
        $clientPetsCount = [];
        foreach ($clients as $client) {
            // Count pets associated with each client
            $clientPetsCount[$client->getId()] = count(array_filter($pets, function ($pet) use ($client) {
                return $pet->getUser() === $client; // Assuming Pet has a getOwner method that links to the User
            }));
        }

        // Group pets by client
        $clientPets = [];
        foreach ($clients as $client) {
            $clientPets[$client->getId()] = array_filter($pets, function ($pet) use ($client) {
                return $pet->getUser() && $pet->getUser()->getId() === $client->getId();
            });
        }

        return $this->render('admin/clients/clients.html.twig', [
            'users' => $clients,
            'clientPetsCount' => $clientPetsCount,
            'clientPets' => $clientPets, // Pass the pets for each client
        ]);
    }





    // Méthode pour ajouter un nouvel utilisateur avec le rôle admin
    #[Route('/user/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Crée un nouvel utilisateur
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Ajouter le rôle admin à l'utilisateur
            $user->setRoles(['ROLE_ADMIN']); // Définit le rôle comme 'admin'

            // Vous pouvez également définir un mot de passe par défaut si nécessaire, sinon il faudra le renseigner via le formulaire
            // $user->setPassword($passwordHasher->hashPassword($user, 'motDePasseParDefaut'));

            // Enregistrer l'utilisateur dans la base de données
            $entityManager->persist($user);
            $entityManager->flush();

            // Rediriger vers la liste des utilisateurs après l'ajout
            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }





    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }


    #[Route('/user/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->request->get('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }


}
