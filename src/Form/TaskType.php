<?php

namespace App\Form;

use App\Entity\Task;
use App\Entity\User;
use App\Entity\Project;
use App\Entity\MissionStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class TaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
{
    $builder
        ->add('title')
        ->add('description')
        ->add('status', EnumType::class, [
            'class' => MissionStatus::class,
            'choice_label' => function(MissionStatus $status) {
                // Personnalisation des libellés de statut
                return match($status) {
                    MissionStatus::STATUS_PENDING => 'En attente',
                    MissionStatus::STATUS_IN_PROGRESS => 'En cours',
                    MissionStatus::STATUS_COMPLETED => 'Terminé',
                    MissionStatus::STATUS_FAILED => 'Échoué',
                    // Ajoutez d'autres cas si nécessaire
                };
            }
        ])
        ->add('startAt', null, [
            'widget' => 'single_text'
        ])
        ->add('endAt', null, [
            'widget' => 'single_text'
        ])
        ->add('project', EntityType::class, [
            'class' => Project::class,
            'choice_label' => 'name',
            'choice_value' => 'id'
        ])
        ->add('assignedTo', EntityType::class, [
            'class' => User::class,
            'choice_label' => 'name',
            'choice_value' => 'id'
        ])
    ;
}


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Task::class,
        ]);
    }
}
