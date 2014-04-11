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
       /* $code = & $_GET['code'];
        if (isset($code)) {

            // Send http get request to retrieve VK code
            $token = $this->post($this->tokenURL, array(
                'client_id' => $this->appCode,
                'client_secret' => $this->appSecret,
                'code' => $code,
                'redirect_uri' => $this->returnURL(),
                'grant_type'    => 'authorization_code' // google add grant type
            ));

            // take user's information using access token
            if (isset($token['access_token'])) {
                $userInfo = $this->get($this->userURL, array(
                    'access_token' => $token['access_token']
                ));
                $this->setUser($userInfo);
                //  trace($this->user);
            }
        }*/

        parent::__token();
    }

    public function setUser(array $user)
    {
        $this->user = new User();

        $this->user->birthday = isset($user['birthday'])?$user['birthday']:0;
        $this->user->email = $user['email'];
        $this->user->gender = $user['gender'];
        $this->user->locale = $user['locale'];
        $this->user->name = $user['given_name'];
        $this->user->surname = $user['family_name'];
        $this->user->socialID = $user['id'];

        parent::setUser($user);
    }
}
 