<?php

namespace App\Controller;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\Project;
use App\Form\ProjectType;
use App\Repository\ProjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\LogicException;

#[Route('/project')]
final class ProjectController extends AbstractController{
    #[Route(name: 'app_project_index', methods: ['GET'])]
    public function index(Security $security, ProjectRepository $projectRepository): Response
    {
        // Récupérer l'utilisateur connecté
        $user = $security->getUser();

        if (!$user) {
            throw $this->createAccessDeniedException('Vous devez être connecté pour voir vos projets.');
        }

        // Récupérer l'ID de l'utilisateur connecté
        $userId = $user->getId();

        // Récupérer les projets associés aux équipes de cet utilisateur
        $projects = $projectRepository->findByUserTeams($userId);
        
        return $this->render('project/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    #[Route('/new', name: 'app_project_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Définir la date de création du projet
            $project->setCreatedAt(new \DateTime());

            // Récupérer l'équipe liée au projet
            $team = $project->getTeam(); // Assure-toi que tu as un setter/getter pour l'équipe

            if ($team) {
                // Activer l'équipe
                $team->setActive(true);
                // Associer ce projet comme le projet actuel de l'équipe
                $team->setCurrentProject($project);
            }

            // Enregistrer le projet et l'équipe
            $entityManager->persist($project);
            $entityManager->flush();

            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/new.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }


    #[Route('/{id}', name: 'app_project_show', methods: ['GET'])]
    public function show(Project $project): Response
    {
        return $this->render('project/show.html.twig', [
            'project' => $project,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_project_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $team = $project->getTeam(); // Assure-toi que tu as un setter/getter pour l'équipe

            if ($team) {
                // Activer l'équipe
                $team->setActive(true);
                // Associer ce projet comme le projet actuel de l'équipe
                $team->setCurrentProject($project);
            }
            
            $entityManager->flush();

            return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('project/edit.html.twig', [
            'project' => $project,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_project_delete', methods: ['POST'])]
    public function delete(Request $request, Project $project, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$project->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($project);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_project_index', [], Response::HTTP_SEE_OTHER);
    }
}
