<?php
namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ProfileController extends AbstractController
{
    #[Route('/profile', name: 'app_profile')]
    #[IsGranted('ROLE_USER')] // Assurez-vous que seuls les utilisateurs connectés peuvent accéder à cette page
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Récupération de l'utilisateur connecté
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException('You must be logged in to edit your profile.');
        }

        // Créer un formulaire lié à l'utilisateur connecté
        $form = $this->createForm(ProfileType::class, $user);

        // Gérer la requête de soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Sauvegarder les modifications dans la base de données
            $entityManager->flush();

            // Message flash pour indiquer la réussite de la modification
            $this->addFlash('success', 'Your profile has been updated successfully!');

            // Rediriger l'utilisateur après la modification (optionnel)
            return $this->redirectToRoute('app_profile');
        }

        // Affichage du formulaire dans la vue Twig
        return $this->render('profile/index.html.twig', [
            'controller_name' => 'ProfileController',
            'form' => $form->createView(), // Envoi du formulaire à la vue
        ]);
    }
}
