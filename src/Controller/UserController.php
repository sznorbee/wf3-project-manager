<?php
namespace App\Controller;

use Twig\Environment;
use App\Entity\User;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class UserController
{
    public function registerUser
                    (
                        Environment $twig,
                        FormFactoryInterface $factory,
                        Request $request,
                        ObjectManager $manager,
                        SessionInterface $session,
                        UrlGeneratorInterface $urlGenerator,
                        \Swift_Mailer $mailer
                    )
    {
       $user = new User();
       
       $builder = $factory->createBuilder(FormType::class, $user);
       $builder->add('username', TextType::class, ['required' => true])
                ->add('firstname', TextType::class, ['required' => true])
                ->add('lastname', TextType::class, ['required' => true])
                ->add('email', EmailType::class, ['required' => true])
                ->add('password', RepeatedType::class, 
                       [
                           'type' => PasswordType::class,
                           'invalid_message' => 'Passwords are not the same',
                           'first_options' => ['label' => 'Password'],
                           'second_options' => ['label' => 'Repeat Password'],
                           'required' => true  
                       ]
                      )
                ->add('send', SubmitType::class,
                   ['attr' => ['class' => 'btn btn-success btn-block']
                   ]
                   );
       
       $form = $builder->getForm();
       $form->handleRequest($request);
       
       if ($form->isSubmitted() && $form->isValid())
       {
           $manager->persist($user);
           $manager->flush();
           
           
           $message = new \Swift_Message();
           $message->setFrom('wf3pm@localhost.com')
                    ->setTo($user->getEmail())
                    ->setSubject('Validate your account!')
                    ->setBody
                        (
                        $twig->render
                            (
                                'mail/accountCreation.html.twig',
                                ['user' => $user]
                            )
                        );
           
           $mailer->send($message);
                        
           $session->getFlashBag()->add('info', 'Ok, you are registered');
           
           return new RedirectResponse($urlGenerator->generate('homepage'));
       }
       return new Response
                  (
                    $twig->render
                    ('User\registerUser.html.twig',
                        ['registerUserFormular' => $form->createView()]
                    )  
                  );
    }

    public function activateUser()
    {
        return new Response('Hello it is ok');
    }
}

