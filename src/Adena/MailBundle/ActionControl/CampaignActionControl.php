<?php

namespace Adena\MailBundle\ActionControl;

use Adena\MailBundle\Entity\Campaign;

/**
 * Class CampaignActionControl
 * This Service is used to check the allowed actions for a given campaign status
 *
 * @package Adena\MailBundle\ActionControl
 */
class CampaignActionControl
{

    const TEST          = 'test';
    const SEND          = 'send';
    const START_RESUME  = 'start_resume';
    const RESUME        = 'resume';
    const EDIT          = 'edit';

    public function isAllowed($action, Campaign $campaign)
    {
        switch ($action){
            case self::TEST:
                return $this->_canTest($campaign);
            case self::SEND:
                return $this->_canSend($campaign);
            case self::START_RESUME:
                return $this->_canStartResume($campaign);
            case self::RESUME:
                return $this->_canResume($campaign);
            case self::EDIT:
                return $this->_canEdit($campaign);
             }

        //TODO - If the action is not found, we can return true or false, we still have to decide
        return true;
    }

    private function _canTest(Campaign $campaign)
    {
        if(in_array($campaign->getStatus(), [
                Campaign::STATUS_NEW,
                Campaign::STATUS_TESTED,
            ]
        )){
            return true;
        }

        return false;
    }

    private function _canSend(Campaign $campaign)
    {
        if(in_array($campaign->getStatus(), [
                Campaign::STATUS_TESTED,
            ]
        )){
          return true;
        }

        return false;
    }

    private function _canStartResume(Campaign $campaign)
    {
        if(in_array($campaign->getStatus(), [
                Campaign::STATUS_NEW,
                Campaign::STATUS_TESTED,
                Campaign::STATUS_PAUSED
            ]
        )){
            return true;
        }

        return false;
    }

    private function _canEdit(Campaign $campaign)
    {
        if(in_array($campaign->getStatus(), [
                Campaign::STATUS_NEW,
                Campaign::STATUS_TESTED
            ]
        )){
            return true;
        }

        return false;
    }

    private function _canResume(Campaign $campaign)
    {
        if(in_array($campaign->getStatus(), [
                Campaign::STATUS_PAUSED
            ]
        )){
            return true;
        }

        return false;
    }
}