# php-unifi-controller

Authorise guest logins via the Ubiquiti Networks UniFi controller.

Example:

	// capture details from UniFi controller
	$user_mac_address = $_GET['id'];

    try {
        $controller = new \UniFi\Controller(UNIFI_SERVER, UNIFI_USER, UNIFI_PASS);
        $controller->sendAuthorization($user_mac_address, $authorisation_minutes);
		echo "Authorised OK";
    } catch (Exception $e) {
        echo "Could not send authorization to UniFi controller: " . $e->getMessage();
    }
