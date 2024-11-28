<?php

namespace App\Form;

use App\Entity\Project;
use App\Entity\Team;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TeamType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void 
    {
        $builder
            ->add('name', null, [
                'label' => 'Nom'
            ])
            ->add('members', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name', // Utilisez 'nom' si c'est le nom de votre attribut
                'choice_value' => 'id',
                'multiple' => true,
                'label' => 'Membres'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Team::class,
        ]);
    }
}
