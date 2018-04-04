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
use Symfony\Component\HttpFoundation\Request;

class ProductController
{
    public function addProduct(Environment $twig, FormFactoryInterface $factory, Request $request)
    {
        $product = new Product();
        $builder = $factory->createBuilder(FormType::class, $product);
        $builder->add('name', TextType::class,
                    ['required' => false]
                    )
                ->add('description', TextareaType::class,
                    ['required' => false]
                    )
                ->add('version', TextType::class)
                ->add('submit', SubmitType::class,
                    ['attr' => ['class' => 'btn btn-success btn-block']]);
        
        $form = $builder->getForm();
        
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
            var_dump($product);
        }
        
        return new Response
                    (
                    $twig->render(
                        'Product\addProduct.html.twig',
                        ['formular' => $form->createView()]
                        )
                    );
        
    }
}

