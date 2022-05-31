<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ResetType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('Cancelar', ButtonType::class, [
                'attr' => ['onclick' => "window.location.href='/products';",
                    'class' => 'btn btn-outline-danger float-end btn-sm my-2']
            ])
            ->add('Guardar', SubmitType::class, [
                'label' => 'Guardar',
                'attr' => ['class' => 'btn btn-outline-success btn-sm float-end my-2']
            ])
            ->add('code', TextType::class, ['label' => 'Código', 'attr' => ['class' => 'form-control mb-2', 'style'=>'clear:both']])
            ->add('name', TextType::class, ['label' => 'Nombre', 'attr' => ['class' => 'form-control mb-2']])
            ->add('description', TextareaType::class, ['label' => 'Descripción', 'attr' => ['class' => 'form-control mb-2']])
            ->add('brand', TextType::class, ['attr' => ['label' => 'Marca', 'class' => 'form-control mb-2']])
            ->add('price', NumberType::class, ['attr' => ['label' => 'Precio', 'class' => 'form-control mb-2']])
            ->add('active', CheckboxType::class, ['label' => 'Producto Activo', 'attr' => ['checked' => true, 'style' => 'margin:15px;']])
            ->add('category', EntityType::class, [
                'label' => 'Categoría',
                'attr' => ['class' => 'form-control my-3'],
                'class' => Category::class,
                'choice_label' => 'name'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
