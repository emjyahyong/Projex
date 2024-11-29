<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Entity\MissionStatus;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/task')]
final class TaskController extends AbstractController
{
    private EntityManagerInterface $entityManager;

    // Injection de l'EntityManager via le constructeur
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route(name: 'app_task_index', methods: ['GET'])]
    public function index(Security $security, TaskRepository $taskRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos tâches.');
        }

        // Récupérer l'ID de l'utilisateur connecté
        $userId = $user->getId();

        // Récupérer les tâches assignées à l'utilisateur connecté
        $tasks = $taskRepository->findByAssignedUser($userId);

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/new', name: 'app_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $task = new Task();
    
        // Définir des valeurs par défaut
        $task->setStatus(MissionStatus::STATUS_PENDING); // Par défaut 'En attente'
        $task->setStartAt(new \DateTime()); // Par défaut la date actuelle
    
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($task);
            $this->entityManager->flush();
    
            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }
    
        return $this->render('task/new.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/change-status/{status}', name: 'app_task_change_status')]
    public function changeStatus(int $id, string $status, EntityManagerInterface $entityManager): RedirectResponse
    {
        // Chercher la tâche par son ID
        $task = $entityManager->getRepository(Task::class)->find($id);

        if (!$task) {
            // Si la tâche n'est pas trouvée, afficher un message d'erreur
            $this->addFlash('error', 'Tâche non trouvée.');
            return $this->redirectToRoute('app_task_index');
        }

        // Vérifier que le statut passé est valide
        $validStatuses = ['PENDING', 'IN_PROGRESS', 'COMPLETED', 'FAILED'];
        if (!in_array($status, $validStatuses)) {
            $this->addFlash('error', 'Statut invalide.');
            return $this->redirectToRoute('app_task_index');
        }

        // Mettre à jour le statut de la tâche en fonction du statut fourni
        switch ($status) {
            case 'PENDING':
                $task->setStatus(MissionStatus::STATUS_PENDING);
                break;
            case 'IN_PROGRESS':
                $task->setStatus(MissionStatus::STATUS_IN_PROGRESS);
                break;
            case 'COMPLETED':
                $task->setStatus(MissionStatus::STATUS_COMPLETED);
                $task->setEndAt(new \DateTime());
                break;
            case 'FAILED':
                $task->setStatus(MissionStatus::STATUS_FAILED);
                $task->setEndAt(new \DateTime());
                break;
        }

        // Enregistrer les modifications dans la base de données
        $entityManager->flush();

        // Rediriger vers la page de la tâche après la mise à jour
        return $this->redirectToRoute('app_task_show', ['id' => $task->getId()]);
    }

    #[Route('/{id}', name: 'app_task_show', methods: ['GET'])]
    public function show(Task $task): Response
    {
        return $this->render('task/show.html.twig', [
            'task' => $task,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Task $task): Response
    {
        $form = $this->createForm(TaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer les modifications dans la base de données
            $this->entityManager->flush();

            return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_task_delete', methods: ['POST'])]
    public function delete(Request $request, Task $task): Response
    {
        // Vérification CSRF
        if ($this->isCsrfTokenValid('delete'.$task->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($task);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('app_task_index', [], Response::HTTP_SEE_OTHER);
    }
}
