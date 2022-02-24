<?php
namespace App\Form;

use App\Entity\Categorie;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

class DvdFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options):void
    {
        $builder
        ->add('titre', TextType::class, ['label' => 'Titre', 'attr' => ['class' => 'form-control']])
        ->add('date', DateType::class, ['label' => 'Date de sortie', "widget" => "single_text", 'attr' => ['class' => 'form-control']])
        ->add('realisateur', TextType::class, ['label' => 'Réalisateur', 'attr' => ['class' => 'form-control']])
        ->add('description', TextareaType::class, ['label' => 'Durée', 'attr' => ['class' => 'form-control']])
        ->add('cover', FileType::class, 
            [
                "label" => "Ajouter une affiche : ",
                "mapped" => false,
                "required" => false,
                "constraints" => [new File([
                    'maxSize' => '1024k',
                    'mimeTypes' => ['image/png', 'image/jpeg'],
                    'mimeTypesMessage' => 'Veuillez choisir une image au format jpeg ou png',])],
                ])
        ->add('categorie', EntityType::class,[
            'label' => 'Catégorie :',
            'required' => false,
            'class' => Categorie::class,
            'choice_label' => 'nom',
            'attr' => ['class' => 'form-control my-2']])
        ->add('save', SubmitType::class, ['label' => 'Enregistrer', 'attr' => ['class' => ' btn my-3']])
        ->getForm();
    }
}