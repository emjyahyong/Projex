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
            ->add('title', null, [
                'label' => 'Titre'
            ])
            ->add('description', null, [
                'label' => 'Description'
            ])
            ->add('status', EnumType::class, [
                'class' => MissionStatus::class,
                'label' => 'Statut',
                'choice_label' => function(MissionStatus $status) {
                    return match($status) {
                        MissionStatus::STATUS_PENDING => 'En attente',
                        MissionStatus::STATUS_IN_PROGRESS => 'En cours',
                        MissionStatus::STATUS_COMPLETED => 'Terminé',
                        MissionStatus::STATUS_FAILED => 'Échoué',
                    };
                }
            ])
            ->add('startAt', null, [
                'widget' => 'single_text',
                'label' => 'Date de début'
            ])
            ->add('endAt', null, [
                'widget' => 'single_text', 
                'label' => 'Date de fin'
            ])
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => 'Projet'
            ])
            ->add('assignedTo', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'name',
                'choice_value' => 'id',
                'label' => 'Assigné à'
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
