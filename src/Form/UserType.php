<?php

namespace App\Form;

use App\Entity\Team;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void 
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom'
            ])
            ->add('email', null, [
                'label' => 'Adresse électronique'
            ])
            ->add('password', null, [
                'label' => 'Mot de passe'
            ])
            ->add('teams', EntityType::class, [
                'class' => Team::class, 
                'choice_label' => 'name', // Changé de 'id' à 'name' pour afficher le nom de l'équipe
                'multiple' => true,
                'label' => 'Équipes'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
