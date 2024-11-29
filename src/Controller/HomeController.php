<?php

namespace App\Controller;

use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
public function index(ProjectRepository $projectRepository, TaskRepository $taskRepository): Response
{
    // Récupérer l'utilisateur connecté
    $user = $this->getUser();

    // Si l'utilisateur est connecté
    if ($user) {
        // Récupérer les équipes de l'utilisateur
        $teams = $user->getTeams();

        // Convertir les équipes en tableau simple
        $teamArray = [];
        foreach ($teams as $team) {
            $teamArray[] = $team;
        }

        // Récupérer les projets associés aux équipes de l'utilisateur
        $projects = $projectRepository->findBy(['team' => $teamArray]);

        // Ou utilisez la méthode spécifique du repository que vous avez déjà créée
        // $projects = $projectRepository->findByTeams($teamArray);

        // Compter le nombre de projets actifs pour les équipes de l'utilisateur
        $activeProjectsCount = count($projects);

        // Récupérer les tâches qui concernent les projets de ces équipes
        $pendingTasks = $taskRepository->findBy(['project' => $projects, 'status' => 'PENDING']);

        // Compter le nombre de tâches en attente
        $pendingTasksCount = count($pendingTasks);

        // Compter les membres actifs dans les équipes de l'utilisateur
        $activeMembersCount = 0;
        foreach ($teams as $team) {
            $activeMembersCount += count($team->getMembers());
        }

        return $this->render('home/index.html.twig', [
            'active_projects_count' => $activeProjectsCount,
            'pending_tasks_count' => $pendingTasksCount,
            'active_members_count' => $activeMembersCount,
        ]);
    }

    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    return $this->redirectToRoute('app_login');
}
}
