<?php

namespace App\Service\Traits;


use Swift_Mailer;

trait TraitMailer
{
    private $mailer;

    /**
     * @return Swift_Mailer
     */
    public function getMailer()
    {
        return $this->mailer;
    }

    /**
     * @param Swift_Mailer $mailer
     */
    public function setMailer(Swift_Mailer $mailer): void
    {
        $this->mailer = $mailer;
    }
}