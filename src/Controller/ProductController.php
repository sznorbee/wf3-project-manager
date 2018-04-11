<?php
namespace App\Controller;


use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Symfony\Component\Form\FormFactoryInterface;
use App\Entity\CommentFile;
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
use App\Entity\Comment;
use App\Form\CommentType;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Routing\Generator\UrlGenerator;

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
           
           return new RedirectResponse
                      (
                          $urlGenerator->generate('homepage')
                      );
        }
        
        return new Response
                    (
                    $twig->render(
                        'Product\addProduct.html.twig',
                        ['formular' => $form->createView()]
                        )
                    );
        
    }
    
    public function listProduct
                    (
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
    
    public function detailProduct
                    ( Environment $twig,
                      ProductRepository $repository,
                      FormFactoryInterface $factory,
                      TokenStorageInterface $tokenStorage,
                      Request $request,
                      ObjectManager $manager,
                      UrlGeneratorInterface $urlGenerator
                    )
    {
        $idProduct = $_GET['id'];
        $product = $repository->findOneById($idProduct);
        
        $comment = new Comment();
        $form = $factory->create
                            (
                                CommentType::class,
                                $comment,
                                ['stateless' => true]
                             );
        
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) 
        {
            $tmpCommentFile = [];
            
            foreach ($comment->getFiles() as $fileArray) 
            {
                foreach ($fileArray as $file)
                {
                    $name = sprintf(
                        '%s.%s',
                        Uuid::uuid1(),
                        $file->getClientOriginalExtension()
                        );
                    
                    $commentFile = new CommentFile();
                    $commentFile->setComment($comment)
                    ->setMimeType($file->getMimeType())
                    ->setName($file->getClientOriginalName())
                    ->setFileUrl('/upload/'.$name);
                    
                    $tmpCommentFile[] = $commentFile;
                    
                    $file->move(
                        __DIR__.'/../../public/upload',
                        $name
                        );
                    
                    $manager->persist($commentFile);
                }
                
            }
            
            $token = $tokenStorage->getToken();
            
   
            if (!$token){
                throw new \Exception();
            }
            $user = $token->getUser();
            if (!$user){
                throw new \Exception();
            }
            
            $comment->setFiles($tmpCommentFile)
            ->setAuthor($user)
            ->setProduct($product);
            
            
            $manager->persist($comment);
            $manager->flush();
            
            return new RedirectResponse
                       (
                           $urlGenerator->generate('detail_product', ['id' => $product->getId()])
                       );
        }
    
        return new Response
        (
            $twig->render
                   (
                      'Product\detailProducts.html.twig',
                       ['product' => $product,
                         'formFile' => $form->createView()
                       ]
                       
                   )
        );
    }
    
   
}

