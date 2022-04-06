<?php

namespace Csv\Controller;

use Base\Controller\BaseController;
use Application\Entity\Page;

class AdminController extends BaseController
{

    protected $view;

    public function indexAction()
    {
        $this->view = $this->getView();

        return $this->view;
    }

    // important
    public function setRequest($req)
    {
        $this->request = $req;
    }

    protected function setHeader($title)
    {
        $title .= date("d-m-Y");
        header("Content-type: text/csv");
        header("Content-disposition: attachment;filename={$title}.csv");
    }

    public function listAllNewsletterAction()
    {
        $users = $this->getEm()->getRepository('\Application\Entity\Newsletter')->findBy(array('confirmed' => 1));
        $title = 'nieuwsbrief-inschrijvingen';
        $this->setHeader($title);

        $out = fopen('php://output', 'w');

        fputcsv($out, array("e-mail"));

        foreach ($users as $user)
        {
            fputcsv($out, array("{$user->getEmail()}"));
        }

        fclose($out);
        exit();
    }

    public function listAllAction()
    {
        $all_users = $this->getEm()->getRepository('\TH\ZfUser\Entity\UserAccount')->findBy(array('deleted' => 0));
        $title = 'alle-gebruikers';
        $this->processSettingUsers($all_users, $title);
    }

	public function listAllPagesAction()
	{
		$this->writePages(
			$this->getEm()->getRepository(
				'\Application\Entity\Page'
			)->findAll(),
			'alle-paginas'
		);
	}

		private function writeCSV($header, $items, $processor) {
				$out = fopen('php://output', 'w');
        fputcsv($out, $header);

        foreach ($items as $item)
					fputcsv($out, $processor($item));
				
        fclose($out);
        exit();
		}
		
		private function writePages($pages, $title)
    {
			$this->setHeader($title);
			$this->writeCSV(
				array("url", "voornaam", "achternaam", "geboorte", "overlijden", "aangemaakt", "geplubliceerd", "publicatiedatum", "status", "prive", "verwijderd", "verwijderingsdatum", "geslacht", "land", "woonplaats", "eigenaar - voornaam", "eigenaar - achternaam", "eigenaar - email"),
				$pages,
				function(Page $page) {
					$copy = $page->getArrayCopy();
					return array(
						$copy['url'],
						$copy['firstname'],
						$copy['lastname'],
						$copy['dateofbirth'],
						$copy['dateofdeath'],
						$copy['creationdate'],
						$copy['publishdate'] === '' ? "nee" : "ja",
						$copy['publishdate'],
						$copy['status'],
						$copy['private'] ? "ja" : "nee",
						$page->getDeletedAt() instanceof \DateTime ? "nee" : "ja",
						$page->getDeletedAt() instanceof \DateTime ? "" : $page->getDeletedAt(),
						$copy['gender'] === 'male' ? 'man' : ($copy['gender'] === 'female' ? 'vrouw' : $copy['gender']),
						$copy['country'],
						$copy['residence'],
						$page->getUser()->getProfile()->getFirstName(),
						$page->getUser()->getProfile()->getLastName(),
						$page->getUser()->getEmail()
					);
				}
			);
    }

    public function listNewFunctionsAction()
    {
        $setting_users = $this->getEm()->getRepository('\Application\Entity\UserDashboardSettings')->findBy(array('receiveUpdates' => 1));
        $title = 'nieuwe-functies-en-updates-inschrijvingen';
        $this->processSettingUsers($setting_users, $title);
    }

    public function listTipsAction()
    {
        $setting_users = $this->getEm()->getRepository('\Application\Entity\UserDashboardSettings')->findBy(array('receiveTips' => 1));
        $title = 'tips-inschrijvingen';
        $this->processSettingUsers($setting_users, $title);
    }

    private function processSettingUsers($valid_users, $title)
    {
        $this->setHeader($title);

        $out = fopen('php://output', 'w');

        fputcsv($out, array("geslacht", "naam", "plaats", "taal", "land", "e-mail", "geboortedatum"));

        foreach ($valid_users as $valid_user)
        {
            $user = $valid_user instanceof \TH\ZfUser\Entity\UserAccount ? $valid_user : $valid_user->getUser();
            $profile = $user->getProfile();

            if (!$user->getDeleted())
            {
                $date_of_birth = $profile->getDateofbirth() ? date_format($profile->getDateofbirth(), 'Y-m-d') : '';
                
                fputcsv($out, array(
                    "{$profile->getGender()}",
                    "{$profile->getName()}",
                    "{$profile->getResidence()}",
                    "{$profile->getLanguage()}",
                    "{$profile->getCountry()}",
                    "{$user->getEmail()}",
                    "{$date_of_birth}"
                ));
            }
        }
        fclose($out);

        exit();
    }

}
