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
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Repository\ProductRepository;

class ProductController
{
    public function addProduct(Environment $twig,
                               FormFactoryInterface $factory,
                               Request $request,
                               ObjectManager $manager,
                               SessionInterface $session,
                               UrlGeneratorInterface $urlGenerator
                              )
    {
        $product = new Product();
        $builder = $factory->createBuilder(FormType::class, $product);
        $builder->add('name', TextType::class,
                    ['required' => false,
                     'label' => 'FORM.PRODUCT.NAME',
                    ]
                    )
                ->add('description', TextareaType::class,
                    ['required' => false,
                     'label' => 'FORM.PRODUCT.DESCRIPTION',
                    ]
                    )
                ->add('version', TextType::class,
                    ['label' => 'FORM.PRODUCT.VERSION']
                    )
                ->add('submit', SubmitType::class,
                    ['attr' => ['class' => 'btn btn-success btn-block'],
                     'label' => 'FORM.PRODUCT.SUBMIT'
                    ]);
        
        $form = $builder->getForm();
        
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid())
        {
           $manager->persist($product);
           $manager->flush();
           
           $session->getFlashBag()->add('info', 'Ok, Project is created!');
           
           return new RedirectResponse($urlGenerator->generate('homepage'));
        }
        
        return new Response
                    (
                    $twig->render(
                        'Product\addProduct.html.twig',
                        ['formular' => $form->createView()]
                        )
                    );
        
    }
    
    public function listProduct(
                        Environment $twig,
                        ProductRepository $repository
                     
                    )
    {
       

        return new Response
        (
            $twig->render(
                'Product\listProducts.html.twig',
                ['product' => $repository->findAll()]
                )
            );
        
    }
}

