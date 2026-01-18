<?php

namespace App\Form;

use App\Entity\TypeBillet;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeBilletType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomType', ChoiceType::class, [
                'label' => 'Type de billet',
                'choices' => [
                    'Standard' => 'Standard',
                    'VIP' => 'VIP',
                    'Gratuit' => 'Gratuit',
                ],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix (MAD)',
                'attr' => ['class' => 'form-control', 'step' => '0.01'],
            ])
            ->add('quantiteTotale', IntegerType::class, [
                'label' => 'Quantité totale',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('statut', ChoiceType::class, [
                'label' => 'Statut',
                'choices' => [
                    'Actif' => 'actif',
                    'Inactif' => 'inactif',
                ],
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypeBillet::class,
        ]);
    }
}
