<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\Commande;
use App\Entity\Livraison;
use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
           
            ->add('client', ClientType::class)
            ->add('livraison', LivraisonType::class,[
                'required'=>false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
