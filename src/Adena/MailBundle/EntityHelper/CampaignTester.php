<?php

namespace Adena\MailBundle\EntityHelper;

use Adena\MailBundle\Entity\Campaign;
use Adena\MailBundle\Entity\MailingList;

class CampaignTester
{
    /**
     * @var \Twig_Environment
     */
    private $twig;
    /**
     * @var \Adena\MailBundle\EntityHelper\MailingListDataFetcher
     */
    private $dataFetcher;

    public function __construct(
        \Twig_Environment $twig,
        MailingListDataFetcher $dataFetcher)
    {
        $this->twig        = $twig;
        $this->dataFetcher = $dataFetcher;
    }

    public function test(Campaign $campaign)
    {
        $debugTwig = clone $this->twig;
        $debugTwig->enableDebug();
        $debugTwig->enableStrictVariables();

        $twigTemplate = $debugTwig->createTemplate($campaign->getEmail()->getTemplate());

        /** @var MailingList $mailingList */
        foreach ($campaign->getMailingLists() as $mailingList) {
            $data = $this->dataFetcher->getFirstRow($mailingList);

            try {
                echo $twigTemplate->render($data);
            } catch (\Exception $e) {
                $variableName = explode(' in ', $e->getMessage())[0];
                throw new \Exception($variableName." in the MailingList: ".$mailingList->getName());
            }
        }

        return true;
    }

}