This plugin provides a `MailerComponent` extending the core `EmailComponent`. `MailerComponent` can then be extended to define custom mailers in which methods sets up their own emails. This avoids bloating up controllers with a bunch of emails methods in `AppController` and allows you to easily send emails in awesomely sexy one-liners : `$this->UserMailer->sendSignup($user)`.

It also replaces the debug mode by writing to the `tmp/logs/error.log` file. Easy debugging with `tail -f` or Console rocks.

## Installation

- Clone from github : in your plugin directory type

    `git clone git://github.com/msadouni/cakephp-mailer-plugin.git mailer`

- Add as a git submodule : in your app directory type

    `git submodule add git://github.com/msadouni/cakephp-mailer-plugin.git plugins/mailer`

- Download an archive from github and extract it in `/plugins/mailer`

## Usage

Create a custom mailer in you `controllers/components` folder :

    // controllers/components/user_mailer.php

    <?php

    App::import('Plugin', 'Mailer.Mailer');

    class UserMailerComponent extends MailerComponent {

        /**
         * Defines the params for a signup email
         */
        function signup($user) {
            if (empty($user['User']['email'])) {
                return false;
            }
            $this->from = 'do-not-reply@example.com';
            $this->to = $user['User']['email'];
            $this->subject = "Welcome !";
            $this->Controller->set('user', $user);
        }

        /**
         * Defines common params for UserMailer emails
         */
        function prepare() {
            parent::prepare();
            $this->sendAs = 'text';
        }
    }
    ?>

Use the mailer in the controller :

    // controllers/users_controller.php

    <?php

    class UsersController extends AppController {

        var $components = array('UserMailer');

        function signup() {
            $user = $this->User->signup($this->data);
            if (!empty($user)) {
                $this->UserMailer->sendSignup($user);
            }
        }
    }
    ?>

The mailer automagically searches for a `views/elements/email/<format>/<mailer_name>/<method_name>.ctp` template. For this example it would use `views/elements/email/text/user_mailer/signup.ctp`.

The magic `sendSomeMethod` first calls `prepare`, either the basic one in `MailerComponent` or your overriden version in `CustomMailerComponent`. It then calls `someMethod` in the mailer wich sets up the email, and finally calls `send`.

While automagic stuff is fun, we sometimes need to override it. No problem. A `MailerComponent` method can call `setTemplate('another_folder/another_template')` :

    // controllers/components/user_mailer.php

    function signup($user) {
        if (empty($user['User']['email'])) {
            return false;
        }
        $this->setTemplate('cookie_mailer/destroy_account'); // Yeah it's silly but we can do it
        $this->from = 'do-not-reply@example.com';
        $this->to = $user['User']['email'];
        $this->subject = "Welcome !";
        $this->Controller->set('user', $user);
    }

If for some reason you want to use the automatic template manually, you can to :

    // controllers/components/user_mailer.php

    function signup($user) {
        if (empty($user['User']['email'])) {
            return false;
        }
        $this->setTemplate(); // Useless because send() does it for us, but we can do it here too
        $this->from = 'do-not-reply@example.com';
        $this->to = $user['User']['email'];
        $this->subject = "Welcome !";
        $this->Controller->set('user', $user);
    }

Feel free to fork it and send patches if you find the plugin useful !
