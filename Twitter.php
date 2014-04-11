<?php
/**
 * Created by Vitaly Iegorov <egorov@samsonos.com>
 * on 10.04.14 at 17:17
 */

namespace samson\social;

/**
 *
 * @author Vitaly Iegorov <egorov@samsonos.com>
 * @copyright 2013 SamsonOS
 * @version
 */
class Twitter extends Core
{
    public $id = 'twitter';

    public $requirements = array('activerecord', 'oauth');

    public $dbIdField = 'tw_id';

    public function __HANDLER()
    {
        /* Build TwitterOAuth object with client credentials. */
        $connection = new TwitterOAuth($this->appCode, $this->appSecret);

        /* Get temporary credentials. */
        $request_token = $connection->getRequestToken($this->returnURL());

        /* If last connection failed don't display authorization link. */
        switch ($connection->http_code) {
            case 200:
                /* Save temporary credentials to session. */
                $_SESSION['oauth_token'] = $token = $request_token['oauth_token'];
                $_SESSION['oauth_token_secret'] = $request_token['oauth_token_secret'];

                /* Build authorize URL and redirect user to Twitter. */
                $url = $connection->getAuthorizeURL($token);
                header('Location: ' . $url);
                break;
            default:
                /* Show notification if something went wrong. */
                echo 'Could not connect to Twitter. Refresh the page or try again later.';
        }
    }

    public function __token()
    {
        /* Create a TwitterOauth object with consumer/user tokens. */
        $connection = new TwitterOAuth($this->appCode, $this->appSecret, $_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);

        /* Request access tokens from twitter */
        $access_token = $connection->getAccessToken($_REQUEST['oauth_verifier']);

        /* Get logged in user to help with tests. */
        $user = $connection->get('account/verify_credentials');

        // Covert user data to generic user object
        if ($user) {
            $this->setUser((array)$user);
        }

        parent::__token();
    }

    public function setUser(array $user)
    {
        $this->user = new User();

        // Separate name and second name
        $name = explode(' ', $user['name']);

        $this->user->birthday = isset($user['birthday'])?$user['birthday']:0;
        $this->user->locale = $user['lang'];
        $this->user->name = $name[0];
        $this->user->surname = isset($name[1]) ? $name[1] : '';
        $this->user->socialID = $user['id'];

        parent::setUser($user);
    }
}
 