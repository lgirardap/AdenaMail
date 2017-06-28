<?php

namespace Adena\MailBundle\EntityHelper;

use Adena\CoreBundle\Tools\BackgroundRunner;
use Adena\MailBundle\Entity\Campaign;
use Adena\MailBundle\Entity\MailingList;

class CampaignTester
{
    /** @var \Twig_Environment */
    private $twig;
    /** @var \Adena\MailBundle\EntityHelper\MailingListDataFetcher */
    private $dataFetcher;
    /** @var BackgroundRunner */
    private $backgroundRunner;

    public function __construct(
        \Twig_Environment $twig,
        MailingListDataFetcher $dataFetcher,
        BackgroundRunner $backgroundRunner)
    {
        $this->twig        = $twig;
        $this->dataFetcher = $dataFetcher;
        $this->backgroundRunner = $backgroundRunner;
    }

    /**
     * Makes sure that the provided Campaign can be successfully sent by making sure at all the associated
     * MailingLists have the the required field to render the associated Email.
     *
     * @param Campaign $campaign
     * @param bool $andSend
     *
     * @return bool
     * @throws \Exception
     */
    public function test(Campaign $campaign, $andSend = false)
    {
        $debugTwig = clone $this->twig;
        $debugTwig->enableDebug();
        // This will make Twig throw an exception if one of the needed variable is undefined.
        $debugTwig->enableStrictVariables();

        $twigTemplate = $debugTwig->createTemplate($campaign->getEmail()->getTemplate());

        /** @var MailingList $mailingList */
        foreach ($campaign->getMailingLists() as $mailingList) {
            $data = $this->dataFetcher->getFirstRow($mailingList);

            try {
                $twigTemplate->render((array)$data);
            } catch (\Exception $e) {
                $variableName = explode(' in ', $e->getMessage())[0];
                throw new \Exception($variableName." in the MailingList: ".$mailingList->getName());
            }
        }

        if($andSend){
            $this->sendTestEmail($campaign);
        }

        return true;
    }

    public function sendTestEmail(Campaign $campaign){
        $this->backgroundRunner->runConsoleCommand('adenamail:campaign:test '.$campaign->getId());
    }

}