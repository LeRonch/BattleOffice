<?php

namespace App\Form;

use App\Entity\Livraison;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LivraisonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prenom')
            ->add('nom')
            ->add('adresse_livraison',TextType::class)
            ->add('complement_livraison',TextType::class)
            ->add('ville')
            ->add('code_postal',TextType::class,[
                'attr' => ['pattern' => "^(([0-8][0-9])|(9[0-5])|(2[ab]))[0-9]{3}$", 'maxlength' => 5]
            ])
            ->add('pays', ChoiceType::class, [
                'choices' => [
                    'France' => 'France',
                    'Belgique' => 'Belgique',
                ],
            ])
            ->add('telephone', TextType::class,[
                'attr' => ['pattern' => "^(?:(?:+|00)33[\s.-]{0,3}(?:(0)[\s.-]{0,3})?|0)[1-9](?:(?:[\s.-]?\d{2}){4}|\d{2}(?:[\s.-]?\d{3}){2})$"               , 'maxlength' => 10]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Livraison::class,
        ]);
    }
}
