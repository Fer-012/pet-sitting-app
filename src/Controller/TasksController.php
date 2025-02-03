<?php

namespace App\Controller;

use App\Entity\Sitter;
use App\Entity\Tasks;
use App\Form\TasksType;
use App\Repository\ReservationRepository;
use App\Repository\SitterRepository;
use App\Repository\TasksRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tasks')]
final class TasksController extends AbstractController
{
    #[Route(name: 'app_tasks_index_new', methods: ['GET'])]
    public function index(TasksRepository $tasksRepository, SitterRepository $sitterRepository): Response
    {
        $sitters = $sitterRepository->findAll();
        return $this->render('admin/tasks/index.html.twig', [
            'tasks' => $tasksRepository->findAll(),
            'sitters' => $sitters
        ]);
    }

    #[Route('/new', name: 'app_tasks_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $task = new Tasks();
        $form = $this->createForm(TasksType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();

            return $this->redirectToRoute('app_tasks_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/tasks/new.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_tasks_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Tasks $task, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(TasksType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_tasks_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/tasks/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_tasks_delete', methods: ['POST'])]
    public function delete(Request $request, Tasks $task, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($task);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tasks_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/tasks', name: 'app_tasks_index', methods: ['GET', 'POST'])]
    public function assignTasks(EntityManagerInterface $entityManager, Request $request, ReservationRepository $reservationRepository): Response
    {
        // Fetch all reservations
        $reservations = $reservationRepository->findAll();

        // Fetch tasks with no sitters assigned
        $tasksNotAssigned = $entityManager->getRepository(Tasks::class)->createQueryBuilder('t')
            ->where('t.sitter IS NULL')
            ->getQuery()
            ->getResult();

        // Fetch tasks with sitters assigned
        $tasksAssigned = $entityManager->getRepository(Tasks::class)->createQueryBuilder('t')
            ->where('t.sitter IS NOT NULL')
            ->getQuery()
            ->getResult();

        // Fetch all sitters (for display only)
        $sitters = $entityManager->getRepository(Sitter::class)->findAll();

        if ($request->isMethod('POST')) {
            // Handle task assignment
            $taskId = $request->request->get('task_id');
            $sitterId = $request->request->get('sitter_id');
            $csrfToken = $request->request->get('_token');

            if ($this->isCsrfTokenValid('assign_sitter_' . $taskId, $csrfToken)) {
                $task = $entityManager->getRepository(Tasks::class)->find($taskId);
                $sitter = $entityManager->getRepository(Sitter::class)->find($sitterId);

                if ($task && $sitter) {
                    $task->setSitter($sitter);
                    $task->setStatus('Assigned');
                    $entityManager->flush();

                    $this->addFlash('success', 'Sitter assigned successfully!');
                } else {
                    $this->addFlash('error', 'Invalid task or sitter selection.');
                }
            } else {
                $this->addFlash('error', 'Invalid CSRF token.');
            }
        }

        return $this->render('admin/tasks/index.html.twig', [
            'tasksAssigned' => $tasksAssigned,
            'tasksNotAssigned' => $tasksNotAssigned,
            'sitters' => $sitters,
            'reservations' => $reservations,
        ]);
    }














}
