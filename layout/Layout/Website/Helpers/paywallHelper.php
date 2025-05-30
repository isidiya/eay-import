<?php

namespace Layout\Website\Helpers;

use Layout\Website\Services\ThemeService;

class paywallHelper {

    public static function start_purchase($session_token, $product_package_id, $recurring_payment = 0) {
        $callback_url = ThemeService::ConfigValue('WEBSITE_DOMAIN') . '/' . ThemeService::ConfigValue('PAYWALL_CALLBACK_URL');
        $parameters = array(
            'website_token' => ThemeService::ConfigValue('SUBSCRIPTIONS_API_TOKEN'),
            'session_token' => $session_token,
            'product_package_id' => $product_package_id,
            'recurring_payment' => $recurring_payment,
            'payment_gateway' => 'cybersource',
            'subscriber_ip' => '1.1.1.1',
            'callback_url' => $callback_url);

        $response = paywallHelper::request("start_purchase_order", $parameters);

        return $response;
    }

    public static function website_packages() {

        $parameters = array(
            'website_token' => ThemeService::ConfigValue('SUBSCRIPTIONS_API_TOKEN'),
            'subscriber_ip' => '1.1.1.1');

        $response = paywallHelper::request("website_packages", $parameters);

        return $response;
    }

    public static function purchase_order_status($purchase_order_id) {
        $parameters = array(
            'website_token' => ThemeService::ConfigValue('SUBSCRIPTIONS_API_TOKEN'),
            'purchase_order_id' => $purchase_order_id);

        $response = paywallHelper::request("purchase_order_status", $parameters);

        return $response;
    }

    private static function request($endpoint, $parameters) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => ThemeService::ConfigValue('SUBSCRIPTIONS_API_URL') . '/' . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $parameters,
            CURLOPT_SSL_VERIFYPEER => false,
        ));

        $response_json = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($response_json, true);
        return $response;
    }

}
