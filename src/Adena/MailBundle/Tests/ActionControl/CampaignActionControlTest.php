<?php

namespace Adena\MailBundle\Tests\ActionControl;

use Adena\MailBundle\ActionControl\CampaignActionControl;
use Adena\MailBundle\Entity\Campaign;
use PHPUnit\Framework\TestCase;

class CampaignActionControlTest extends TestCase
{

    public function testIsAllowed()
    {

        // == What can a Campaign with a 'NEW' status
        $campaign = $this->_mockCampaignGetStatus(Campaign::STATUS_NEW);

        $actions = array(
            'test'          => true,
            'send'          => false,
            'start'         => false,
            'start_resume'  => false,
            'resume'        => false,
            'pause'         => false,
            'edit'          => true,
        );

        $this->_testIsAllowedResult($actions, $campaign);

        // == What can a Campaign with a 'TESTING' status
        $campaign = $this->_mockCampaignGetStatus(Campaign::STATUS_TESTING);

        $actions = array(
            'test'          => false,
            'send'          => true,
            'start'         => false,
            'start_resume'  => false,
            'resume'        => false,
            'pause'         => false,
            'edit'          => false,
        );

        $this->_testIsAllowedResult($actions, $campaign);

        // == What can a Campaign with a 'TESTED' status
        $campaign = $this->_mockCampaignGetStatus(Campaign::STATUS_TESTED);

        $actions = array(
            'test'          => true,
            'send'          => false,
            'start'         => true,
            'start_resume'  => true,
            'resume'        => false,
            'pause'         => false,
            'edit'          => true,
        );

        $this->_testIsAllowedResult($actions, $campaign);

        // == What can a Campaign with a 'IN_PROGRESS' status
        $campaign = $this->_mockCampaignGetStatus(Campaign::STATUS_IN_PROGRESS);

        $actions = array(
            'test'          => false,
            'send'          => true,
            'start'         => false,
            'start_resume'  => false,
            'resume'        => false,
            'pause'         => true,
            'edit'          => false,
        );

        $this->_testIsAllowedResult($actions, $campaign);

        // == What can a Campaign with a 'PAUSED' status
        $campaign = $this->_mockCampaignGetStatus(Campaign::STATUS_PAUSED);

        $actions = array(
            'test'          => false,
            'send'          => false,
            'start'         => false,
            'start_resume'  => true,
            'resume'        => true,
            'pause'         => false,
            'edit'          => false,
        );

        $this->_testIsAllowedResult($actions, $campaign);

        // == What can a Campaign with a 'ENDED' status
        $campaign = $this->_mockCampaignGetStatus(Campaign::STATUS_ENDED);

        $actions = array(
            'test'          => false,
            'send'          => false,
            'start'         => false,
            'start_resume'  => false,
            'resume'        => false,
            'pause'         => false,
            'edit'          => false,
        );

        $this->_testIsAllowedResult($actions, $campaign);

    }

    /**
     * Create a new mocked campaign and set the getStatus method
     *
     * @param $returnValue
     *
     * @return \Adena\MailBundle\Entity\Campaign|\PHPUnit_Framework_MockObject_MockObject
     */
    private function _mockCampaignGetStatus($returnValue ){

        $campaign = $this->createMock(Campaign::class);
        $campaign->expects($this->any())
            ->method('getStatus')
            ->willReturn($returnValue);

        return $campaign;
    }

    /**
     * Get an array of action et check if the results expected are actually returned by the Campaign ActionControl method isAllowed
     * for a specific campaign
     *
     * @param $actions
     * @param $campaign
     */
    private function _testIsAllowedResult($actions, Campaign $campaign ){

        $campaignActionControl = new CampaignActionControl();

        foreach($actions as $action=>$result){
            $actionAllowed = $campaignActionControl->isAllowed($action, $campaign);
            if($result === true ){
                $this->assertTrue($actionAllowed, "Error, the action '". $action ."' for the status '".$campaign->getStatus()."' is ALLOWED and should NOT BE ALLOWED");
            } else {
                $this->assertfalse($actionAllowed, "Error, the action '". $action ."' for the status '".$campaign->getStatus()."' is NOT ALLOWED and should BE ALLOWED");
            }
        }
    }
}