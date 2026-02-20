<?php

namespace App\Form;

use App\Entity\Note;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'required' => true,
                'attr' => ['placeholder' => 'Note title'],
            ])
            ->add('content', TextareaType::class, [
                'required' => false,
                'attr' => ['id' => 'note-content', 'rows' => 12],
            ])
            ->add('tags', TextType::class, [
                'required' => false,
                'attr' => ['placeholder' => 'tag1, tag2, tag3'],
                'label' => 'Tags (comma-separated)',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Note::class]);
    }
}
