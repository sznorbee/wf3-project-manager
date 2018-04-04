<?php
namespace App\Controller;


use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Symfony\Component\Form\FormFactoryInterface;
use App\Entity\Product;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ProductController
{
    public function addProduct(Environment $twig, FormFactoryInterface $factory)
    {
        $product = new Product();
        $builder = $factory->createBuilder(FormType::class, $product);
        $builder->add('name', TextType::class)
                ->add('description', TextareaType::class)
                ->add('version', TextType::class)
                ->add('submit', SubmitType::class);
        
        $form = $builder->getForm();
        return new Response
                    (
                    $twig->render(
                        'Product\addProduct.html.twig',
                        ['formular' => $form->createView()]
                        )
                    );
        
    }
}

