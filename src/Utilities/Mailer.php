<?php

namespace LWM\Disc\Utilities;

if (!class_exists('LWM\Disc\Utilities\Mailer')) {
    /**
     * Class Mailer
     * @package LWM\Disc\Utilities
     */
    class Mailer
    {
        /**
         * @var string
         */
        private $email;
        /**
         * @var string
         */
        private $subject;
        /**
         * @var string
         */
        private $message;
        /**
         * @var int
         */
        private $emailInterval = 12;

        /**
         * @param mixed $email
         */
        public function setEmail($email): void
        {
            $this->email = $email;
        }

        /**
         * @param int $emailInterval
         */
        public function setEmailInterval(int $emailInterval): void
        {
            $this->emailInterval = $emailInterval;
        }

        /**
         * @param mixed $subject
         */
        public function setSubject($subject): void
        {
            $this->subject = $subject;
        }

        /**
         * @param mixed $message
         */
        public function setMessage($message): void
        {
            $this->message = $message;
        }

        /**
         * @param $transient
         *
         * @return bool
         */
        public function sendEmail($transient)
        {
            if (
                !Helpers::hasValue($this->message)
                || !Helpers::hasValue($this->subject)
            ) {
                return false;
            }

            if (!Helpers::hasValue($this->email)) {
                $this->email = get_option('admin_email');
            }

            if (false === get_transient($transient)) {
                // make sure not to disturb admin with tons of emails
                set_transient($transient, 'data', HOUR_IN_SECONDS * $this->emailInterval);
                $mailed = wp_mail(
                    $this->email,
                    $this->subject,
                    $this->message,
                    [
                        'from: no-reply@lifewatermedia.com',
                        'Content-Type: text/html; charset=UTF-8',
                        'MIME-Version: 1.0',
                    ]
                );
            }
        }
    }
}
