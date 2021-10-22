<?php

namespace App\Controller;

use App\Entity\Locataire;
use App\Entity\Quittance;
use App\Form\QuittancesType;
use App\Repository\LocataireRepository;
use App\Repository\QuittanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/quittances", name="quittances_")
 * @IsGranted("ROLE_USER")
 */
class QuittancesController extends AbstractController
{
    /**
     * @Route("/new/{loc_id}", name="edit_new_quittance")
     */
    public function editNewQuittance($loc_id, LocataireRepository $locataireRepository, EntityManagerInterface $em, Request $request): Response
    {
        setlocale(LC_TIME, 'fr_FR.utf8','fra');
        date_default_timezone_set('Europe/Paris');
        $date = new \DateTime();

        $locataire = $locataireRepository->find($loc_id);
        $logement = $locataire->getLogement();

        $form = $this->createForm(QuittancesType::class);

        $form->get('quittance_id')->setData($locataire->getQuittances()->count() + 1);
        $form->get('first_day')->setData(1);
        $form->get('last_day')->setData(\Date('t'));
        $form->get('month')->setData(strftime("%B"));
        $form->get('loyer_ttc')->setData($logement->getLoyerTtc());
        $form->get('loyer_hc')->setData($logement->getLoyerHc());
        $form->get('charges')->setData($logement->getCharges());
        $form->get('mode')->setData($locataire->getMode());
        $form->get('solde')->setData($logement->getSolde());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $template = $this->fillQuittanceTemplateFromForm($locataire, $form);
            $dateForFile = $form->get('date')->getData()->format('d-m-Y');
            $file = "quittance_".$dateForFile.'_'. $locataire->getLastName().'_'.$locataire->getLogement()->getId().'_'.uniqid();
            $file = str_replace(" ", "_",$file);
            $this->createQuittanceFile($template, $locataire, $file);

            $quittance = new Quittance();
            $quittance->setFileName($file);
            $quittance->setLocataire($locataire);
            $quittance->setBienImmo($locataire->getLogement());
            $quittance->setCreatedDate($date->setTimezone(new \DateTimeZone("Europe/Paris")));
            $em->persist($quittance);
            $em->flush();

            return $this->redirectToRoute('quittances_render_quittance', [
                'quittance_id' => $quittance->getId()
            ]);
        }

        return $this->render("immo/quittances/edit_quittance.html.twig",[
            'form' => $form->createView(),
            'locataire' => $locataire,
        ]);

    }


    /**
     * @Route("/render/{quittance_id}", name="render_quittance")
     */
    public function renderQuittance(QuittanceRepository $quittanceRepository, $quittance_id){

        $quittance = $quittanceRepository->find($quittance_id);
        $locataire = $quittance->getLocataire();
        $file = $quittance->getFileName();

        if (file_exists('../public/documents/quittances/' . $file . '.pdf')){
            $pdf_exist = true;
        }else{
            $pdf_exist = false;
        }

        return $this->render('immo/quittances/download_file.html.twig',[
            "file_name" => $file,
            "locataire" => $locataire,
            "quittance" => $quittance,
            "pdf_exist" => $pdf_exist,
        ]);
    }

    /**
     * @Route("/{loc_id}", name="edit_current_month_quittance")
     */
    public function editCurrentMonthQuittance($loc_id, LocataireRepository $locataireRepository, EntityManagerInterface $em, QuittanceRepository $quittanceRepository): Response
    {
        $date = new \DateTime();

        $locataire = $locataireRepository->find($loc_id);

        $template = $this->fillQuittanceTemplate($locataire);

        $file = "quittance_" . strftime("%B_") . $locataire->getLastName() . '_' . $locataire->getLogement()->getId();

        $quittance = $quittanceRepository->findOneBy(['file_name' => $file]);

        if (!$locataire->getQuittances()->contains($quittance)){
            $quittance = new Quittance();
            $quittance->setFileName($file);
            $quittance->setLocataire($locataire);
            $quittance->setBienImmo($locataire->getLogement());
            $quittance->setCreatedDate($date->setTimezone(new \DateTimeZone("Europe/Paris")));
            $em->persist($quittance);
            $em->flush();

            $this->createQuittanceFile($template, $locataire, $file);

        }

        if (file_exists('../public/documents/quittances/' . $file . '.pdf')){
            $pdf_exist = true;
        }else{
            $pdf_exist = false;
        }

        return $this->render("immo/quittances/download_file.html.twig",[
            "file_name" => $file,
            "locataire" => $locataire,
            "pdf_exist" => $pdf_exist,
            "quittance" => $quittance,
        ]);

    }


    public function convertWordToPdf($file_name, $loc_id): Response
    {
        $project_dir = $this->getParameter('kernel.project_dir');
//        $chemin = '"%ProgramFiles%\LibreOffice\program\soffice" --headless --convert-to pdf '.$project_dir.'\assets\files\quittances\\';
        $chemin = 'soffice --headless --convert-to pdf '.$project_dir.'\public\documents\quittances\\';
        $cmd = $chemin . $file_name . ' --outdir '.$project_dir.'\public\documents\quittances';

        if (!shell_exec($cmd) == null){
            shell_exec($cmd);
        }

        return $this->redirectToRoute("quittances_edit_current_month_quittance",[
            'loc_id' => $loc_id,
        ]);
    }

    /**
     * @param $file_name
     * @param $file_name_pdf
     * @return Response
     * @Route("/ddl/{file_name}/{loc_id}", name="ddl_quittance")
     */
    public function downloadPdf($file_name, $loc_id, LocataireRepository $locataireRepository, QuittanceRepository $quittanceRepository): Response
    {
        $locataire = $locataireRepository->find($loc_id);
        $quittance = $quittanceRepository->findOneBy(['file_name' => $file_name]);
        return $this->render("immo/quittances/download_file.html.twig",[
            "file_name" => $file_name,
            "locataire" => $locataire,
            "quittance" => $quittance,
        ]);
    }


    /**
     * @param Locataire $locataire
     * @return \PhpOffice\PhpWord\TemplateProcessor
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function fillQuittanceTemplate($locataire)
    {
        setlocale(LC_TIME, 'fr_FR.utf8','fra');
        date_default_timezone_set('Europe/Paris');
        $user = $this->getUser();

        $date = new \DateTime();

        $date->setTimezone(new \DateTimeZone("Europe/Paris"));
        $template = new \PhpOffice\PhpWord\TemplateProcessor("../assets/files/templates/QUITTANCE_TEMPLATE.docx");
        $template->setValue('p_gender', $user->getGender());
        $template->setValue('p_lastname', $user->getLastname());
        $template->setValue('p_firstname', $user->getFirstname());
        $template->setValue("last_name",$locataire->getLastName());
        $template->setValue("first_name",$locataire->getFirstName());
        $template->setValue("gender",$locataire->getGender());
        $template->setValue("building",$locataire->getLogement()->getBuilding());
        $template->setValue("street",$locataire->getLogement()->getStreet());
        $template->setValue("cp",$locataire->getLogement()->getCp());
        $template->setValue("city",$locataire->getLogement()->getCity());
        $template->setValue("date",$date->format('d/m/Y'));
        $template->setValue("mode",$locataire->getMode());
        $template->setValue("loyer_ttc",$locataire->getLogement()->getLoyerTtc());
        $template->setValue("loyer_hc",$locataire->getLogement()->getLoyerHc());
        $template->setValue("charges",$locataire->getLogement()->getCharges());
        $template->setValue("solde",$locataire->getLogement()->getSolde());
        $template->setValue("payment_date",$date->format('d/m/Y'));
        $template->setValue("first_day",'1');
        $template->setValue("last_day",\Date('t'));
        $template->setValue("month",strftime("%B"));

        return $template;
    }


    /**
     * @param Locataire $locataire
     * @return \PhpOffice\PhpWord\TemplateProcessor
     * @throws \PhpOffice\PhpWord\Exception\CopyFileException
     * @throws \PhpOffice\PhpWord\Exception\CreateTemporaryFileException
     */
    public function fillQuittanceTemplateFromForm($locataire, $form)
    {
        setlocale(LC_TIME, 'fr_FR.utf8','fra');
        date_default_timezone_set('Europe/Paris');
        $user = $this->getUser();

        $date = new \DateTime();

        $date->setTimezone(new \DateTimeZone("Europe/Paris"));
        $template = new \PhpOffice\PhpWord\TemplateProcessor("../assets/files/templates/QUITTANCE_TEMPLATE.docx");
        $template->setValue('p_gender', $user->getGender());
        $template->setValue('p_lastname', $user->getLastname());
        $template->setValue('p_firstname', $user->getFirstname());
        $template->setValue("last_name",$locataire->getLastName());
        $template->setValue("first_name",$locataire->getFirstName());
        $template->setValue("gender",$locataire->getGender());
        $template->setValue("building",$locataire->getLogement()->getBuilding());
        $template->setValue("street",$locataire->getLogement()->getStreet());
        $template->setValue("cp",$locataire->getLogement()->getCp());
        $template->setValue("city",$locataire->getLogement()->getCity());
        $template->setValue("date",$form->get('date')->getData()->format('d/m/Y'));
        $template->setValue("mode",$form->get('mode')->getData());
        $template->setValue("loyer_ttc",$form->get('loyer_ttc')->getData());
        $template->setValue("loyer_hc",$form->get('loyer_hc')->getData());
        $template->setValue("charges",$form->get('charges')->getData());
        $template->setValue("solde",$form->get('solde')->getData());
        $template->setValue("payment_date",$form->get('payment_date')->getData()->format('d/m/Y'));
        $template->setValue("first_day",$form->get('first_day')->getData());
        $template->setValue("last_day",$form->get('last_day')->getData());
        $template->setValue("month",$form->get('month')->getData());

        return $template;
    }


    public function createQuittanceFile($template, $locataire, $file)
    {
        $template->setValue("quittance_id", $locataire->getQuittances()->count() + 1);

        //if (!file_exists('../assets/files/quittances/')) {
        //    mkdir('../assets/files/quittances/', 0777, true);
        //}
        if (!file_exists('../public/documents/quittances/')) {
            mkdir('../public/documents/quittances/', 0777, true);
        }

        $template->saveAs("../public/documents/quittances/" . $file . ".docx");
        $word = new \PhpOffice\PhpWord\TemplateProcessor("../public/documents/quittances/".$file.".docx");
        $word->saveAs("../public/documents/quittances/" . $file . ".docx");

        $this->convertWordToPdf($file . ".docx", $locataire->getId());
    }
}
