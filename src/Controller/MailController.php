<?php


namespace App\Controller;

use Twig\Environment;

class MailController
{

    /**
     * NotifMessageconstructor.
     * @param \Swift_Mailer $mailer
     * @param Environment $renderer
     */
    private $mailer;
    private $renderer;

    public function __construct(\Swift_Mailer $mailer, Environment $renderer)
    {
        $this->mailer = $mailer;
        $this->renderer = $renderer;
    }


    public function sendMessage()
    {
        $mail = (new \Swift_Message('Gestion immo'))
            ->setFrom('admin@immo.com')
            /**
             * Ci dessous entrez l'adresse de l'utilisateur concerné : $message->getEmail()
             */
            ->setTo(['chrisbdeveloppeur@gmail.com'])
            ->setBody($this->renderer->render('emails/message.html.twig',[]), 'text/html' );
        $this->mailer->send($mail);
    }

}
