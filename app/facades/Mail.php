<?php

/**
 * Class Mail
 *
 * @method static int send(Swift_Mime_SimpleMessage $message, ?array &$failedRecipients = null)
 * @method static Swift_Mailer channel(?string $channel = null)
 */
class Mail extends Facade
{
    /**
     * {@inheritdoc}
     */
    protected static function getFacadeAccessor()
    {
        return 'services.mail';
    }
}
