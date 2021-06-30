<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('prenom')
            ->add('nom')
            ->add('adresse',TextType::class,[
                'required'=>true,
                'empty_data'=>''
            ])
            ->add('complement')
            ->add('ville')
            ->add('codepostal',TextType::class,[
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
            ->add('email')
            ->add('confirm_email')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
