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


    public function sendQuittance($file_path, $locataire)
    {
        $mail = (new \Swift_Message('Quittance de loyer'))
            ->setFrom('admin@edit-word.com')
            ->setTo($locataire->getEmail())
            ->setBody($this->renderer->render('emails/message.html.twig',[]), 'text/html' );
        $mail->attach(\Swift_Attachment::fromPath($file_path));
        $this->mailer->send($mail);
    }

}
