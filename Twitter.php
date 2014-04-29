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
class Twitter extends \samson\social\Network
{
    public $id = 'twitter';

    public $requirements = array('socialnetwork', 'oauth');

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

    protected function setUser(array $userData, & $user = null)
    {
        $user = new User();

        // Separate name and second name
        $name = explode(' ', $userData['name']);

        $user->birthday = isset($userData['birthday'])?$userData['birthday']:0;
        $user->locale = $userData['lang'];
        $user->name = $userData[0];
        $user->surname = isset($userData[1]) ? $userData[1] : '';
        $user->socialID = $userData['id'];
        $user->photo = $userData['profile_image_url'];

        parent::setUser($userData, $user);
    }
}
 