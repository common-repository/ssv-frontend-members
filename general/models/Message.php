<?php
if (!defined('ABSPATH')) {
    exit;
}

class Message
{
    const NOTIFICATION_MESSAGE = 'notification';
    const ERROR_MESSAGE = 'error';

    public $message;
    public $type;

    /**
     * Message constructor.
     *
     * @param string $message is the
     * @param string $type
     */
    public function __construct($message, $type = Message::NOTIFICATION_MESSAGE)
    {
        $this->message = $message;
        $this->type = $type;
    }

    /**
     * @return string with the message.
     */
    public function __toString()
    {
        return $this->message;
    }

    public function htmlPrint()
    {
        ob_start();
        $class = $this->type == Message::NOTIFICATION_MESSAGE ? '' : 'notification-error';
        ?>
        <div class="mui-panel notification <?php echo $class; ?>">
            <?php echo $this->message; ?>
        </div>
        <?php
        return ob_get_clean();
    }
}
