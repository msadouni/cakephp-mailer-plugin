<?php

App::import('Plugin', 'Mailer.Mailer');

class MailerTestComponent extends MailerComponent {

    function prepare() {
        parent::prepare();
        $this->sendAs = 'text';
    }

    function signup($user) {
        $this->from = 'from@example.com';
        $this->to = 'to@example.com';
        $this->subject = "Welcome !";
        $this->Controller->set($user);
    }

    function withoutTemplate() {
        $this->Controller->MailerTest->from = 'from@example.com';
        $this->Controller->MailerTest->to = 'to@example.com';
        $this->Controller->MailerTest->subject = "Welcome !";
    }

    function withTemplate() {
        $this->Controller->MailerTest->setTemplate('another_template');
        $this->Controller->MailerTest->from = 'from@example.com';
        $this->Controller->MailerTest->to = 'to@example.com';
        $this->Controller->MailerTest->subject = "Welcome !";
    }

    function withAutoTemplate() {
        $this->Controller->MailerTest->setTemplate();
        $this->Controller->MailerTest->from = 'from@example.com';
        $this->Controller->MailerTest->to = 'to@example.com';
        $this->Controller->MailerTest->subject = "Welcome !";
    }
}

class MailerTestController extends Controller {

    var $uses = null;
    var $components = array('MailerTest');
}

class MailerComponentTest extends CakeTestCase {

    function setUp() {
        $this->Controller =& new MailerTestController();
        restore_error_handler();
        @$this->Controller->Component->init($this->Controller);
        set_error_handler('simpleTestErrorHandler');
        $this->Controller->MailerTest->initialize($this->Controller, array());
    }

    function tearDown() {
        restore_error_handler();
    }

    function testTemplateFolder() {
        $this->assertEqual(
            $this->Controller->MailerTest->__templateFolder(),
            'mailer_test',
            "should be the underscored mailer class name : %s"
        );
    }

    function testPrepare() {
        $debug = Configure::read('debug');

        Configure::write('debug', 1);
        $this->Controller->MailerTest->prepare();
        $this->assertEqual(
            $this->Controller->MailerTest->delivery,
            'debug',
            "should change delivery to debug if debug > 0 : %s");

        Configure::write('debug', 0);
        $this->Controller->MailerTest->delivery = 'mail';
        $this->Controller->MailerTest->prepare();
        $this->assertEqual(
            $this->Controller->MailerTest->delivery,
            'mail',
            "should not change delivery if debug = 0 : %s");

        Configure::write('debug', $debug);
    }

    function testSendWithoutTemplate() {
        $this->assertTrue(
            $this->Controller->MailerTest->sendWithoutTemplate(),
            "should send the email : %s"
        );
        $this->assertEqual(
            $this->Controller->MailerTest->template,
            'mailer_test'.DS.'without_template',
            "should use the underscored function name template in the mailer template folder : %s");
    }

    function testSendWithTemplate() {
        $this->assertTrue(
            $this->Controller->MailerTest->sendWithTemplate(),
            "should send the email : %s"
        );
        $this->assertEqual(
            $this->Controller->MailerTest->template,
            'mailer_test'.DS.'another_template',
            "should use the specified template in the mailer template folder : %s");
    }

    function testSendWithAutoTemplate() {
        $this->assertTrue(
            $this->Controller->MailerTest->sendWithAutoTemplate(),
            "should send the email : %s"
        );
        $this->assertEqual(
            $this->Controller->MailerTest->template,
            'mailer_test'.DS.'with_auto_template',
            "should use the underscored function name template in the mailer template folder : %s");
    }

    function testMagicSend() {
        $this->assertTrue(
            $this->Controller->MailerTest->sendSignup(array(
                'User' => array('email' => 'a@b.com')
            )),
            "should call the requested method : %s"
        );
        $this->expectError();
        $this->assertIdentical(
            $this->Controller->MailerTest->sendMissingMethod(array(
                'User' => array('email' => 'a@b.com')
            )),
            null,
            "should trigger an error and return null if the requested method doesn't exist : %s"
        );
    }
}
?>