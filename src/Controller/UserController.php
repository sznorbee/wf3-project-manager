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
use Doctrine\Common\Persistence\ObjectManagerDecorator;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
           $message->setFrom('ooptest@norbertszekeres.eu')
                    ->setTo($user->getEmail())
                    ->setSubject('Validate your account!')
                    ->setBody
                        (
                        $twig->render
                            (
                                'mail/accountCreation.html.twig',
                                ['user' => $user]
                            ),
                        'text/html'
                        );
           
           $mailer->send($message);
                        
           $session->getFlashBag()->add('info', 'Ok, a confirmation email has been sent to complete your registration!');
           
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

    public function activateUser
                        (
                            $token,
                            ObjectManager $manager,
                            UrlGeneratorInterface $urlGenerator,
                            SessionInterface $session
                         )
    {
        $userRepository = $manager->getRepository(User::class);
        $user = $userRepository->findOneByEmailToken($token);
        
        if (!$user)
        {
            throw new NotFoundHttpException('User is not exist, please register first');
         
        }
        $user->setActive(true)
        ->setEmailToken(null);
        
        $manager->flush();
        $session->getFlashBag()
                ->add('info', 'Ok, your email is confirmed, you are registered!');
        
        return new RedirectResponse
                    (
                       $urlGenerator->generate('homepage')
                     );
            
    }
}

