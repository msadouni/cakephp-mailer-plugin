<?php

App::import('Component', 'Email');

/**
 * MailerComponent
 *
 * Extends EmailComponent to allow easy defining and sending of emails
 */
class MailerComponent extends EmailComponent {

/**
 * Prepares the email for sending
 *
 * Calls EmailComponent::reset()
 * and set the delivery to 'debug' if we are in debug mode
 *
 * @access protected
 */
    function _prepare() {
        $this->reset();
        if (Configure::read('debug') > 0) {
            $this->delivery = 'debug';
        }
    }

/**
 * Assigns the $this->template variable
 *
 * Uses the mailer folder's name and method name unless specified
 * @param string $template the name of the template to use
 * @access protected
 */
    function _setTemplate($template = null) {
        if (empty($template)) {
            $backtrace = debug_backtrace();
            if (empty($backtrace[1]['function'])) {
                return $this->template = null;
            }
            $parent = $backtrace[1]['function'];
            $method = '';
            if ($parent == '__call') {
                $method = str_replace('send', '', $backtrace[2]['function']);
            } else {
                $method = $backtrace[1]['function'];
            }
            $template = Inflector::underscore($method);
        }
        $this->template = sprintf('%s%s%s',
            $this->_templateFolder(),
            DS,
            $template
        );
    }

/**
 * Returns the name of the template folder
 *
 * @return string the name of the template folder
 * @access protected
 */
    function _templateFolder() {
        return Inflector::underscore(str_replace(
            'Component', '', $this->toString()
        ));
    }

/**
 * Does the magic sendMethod stuff
 *
 * prepares the email, calls the method to set up the params and sends it
 * @param string $name the name of the method
 * @param array $params the arguments to pass to the method
 * @return boolean
 */
    function __call($method, $params = array()) {
        $method = lcfirst(str_replace('send', '', $method));
        if (!method_exists($this, $method)) {
            trigger_error("MailerComponent::__call() - Method {$method} doesn't exist", E_USER_WARNING);
            return null;
        }
        $this->_prepare();
        call_user_func_array(array($this, $method), $params);
        if (empty($this->template)) {
            $this->_setTemplate();
        }
        return $this->send();
    }

/**
 * Writes the email to the error.log file
 *
 * @return true
 * @access private
 */
    function __debug() {
        $nl = "\n";
        $header = implode($nl, $this->__header);
        $message = implode($nl, $this->__message);
        $fm = $nl;

        if ($this->delivery == 'smtp') {
            $fm .= sprintf('%s %s%s', 'Host:', $this->smtpOptions['host'], $nl);
            $fm .= sprintf('%s %s%s', 'Port:', $this->smtpOptions['port'], $nl);
            $fm .= sprintf('%s %s%s', 'Timeout:', $this->smtpOptions['timeout'], $nl);
        }
        $fm .= sprintf('%s %s%s', 'To:', $this->to, $nl);
        $fm .= sprintf('%s %s%s', 'From:', $this->from, $nl);
        $fm .= sprintf('%s %s%s', 'Subject:', $this->__encode($this->subject), $nl);
        $fm .= sprintf('%s%3$s%3$s%s', 'Header:', $header, $nl);
        $fm .= sprintf('%s%3$s%3$s%s', 'Parameters:', $this->additionalParams, $nl);
        $fm .= sprintf('%s%3$s%3$s%s', 'Message:', $message, $nl);
        $fm .= $nl;

        $this->log($fm);
        return true;
    }
}
?>