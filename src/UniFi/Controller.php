<?php

namespace UniFi;

/**
 * UniFi Controller interaction
 *
 * @author Glen Scott <glen@glenscott.co.uk>
 */
class Controller
{
    private $server;
    private $user;
    private $password;

    public function __construct($server, $user, $password)
    {
        $this->server   = $server;
        $this->user     = $user;
        $this->password = $password;
    }

    public function sendAuthorization($id, $minutes)
    {
        // Start Curl for login
        $ch = curl_init();

        if (!$ch) {
            throw new Exception("Could not initialise curl session");
        }

        // @todo -- race conditions, so generate random cookie file for each session
        $cookie_file = "/tmp/unifi_cookie";

        $options = array(CURLOPT_POST           => true,
                         CURLOPT_COOKIEJAR      => $cookie_file,
                         CURLOPT_COOKIEFILE     => $cookie_file,
                         CURLOPT_SSL_VERIFYPEER => false,
                         CURLOPT_SSL_VERIFYHOST => false,
                         CURLOPT_URL            => $this->server . '/login',
                         CURLOPT_POSTFIELDS     => 'login=login&username=' . $this->user . '&password=' . $this->password,
            );

        if (!curl_setopt_array($ch, $options)) {
            throw new Exception("Could not set curl options");
        }

        if (!curl_exec($ch)) {
            throw new Exception("Could not execute curl session (login): " . curl_error($ch));
        }

        // Send user to authorize and the time allowed
        $data = json_encode(array(
            'cmd'     => 'authorize-guest',
            'mac'     => $id,
            'minutes' => $minutes));

        // Send the command to the API
        curl_setopt($ch, CURLOPT_URL, $this->server . '/api/cmd/stamgr');
        curl_setopt($ch, CURLOPT_POSTFIELDS, 'json='.$data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $res = curl_exec($ch);
        if (!$res) {
            throw new Exception("Could not execute curl session (authorize-guest)");
        }

        // Logout of the UniFi Controller
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        curl_setopt($ch, CURLOPT_URL, $this->server . '/logout');
        
        if (!curl_exec($ch)) {
            throw new Exception("Could not execute curl session (logout)");
        }

        curl_close($ch);
        unset($ch);

        return true;
    }
}
