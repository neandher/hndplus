<?php

class CurlHelper
{

    public static function curl($url, $binary = false, $post = false, $cookie = false, $nobody = false)
    {

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_REFERER, $url);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if ($cookie) {

            $agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)";
            curl_setopt($ch, CURLOPT_USERAGENT, $agent);
            curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
            curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);

        }

        if ($binary) {
            curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        }

        if ($post) {

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            //curl_setopt($ch, CURLOPT_POSTREDIR, 1);
        }

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        if ($nobody) {

            curl_setopt($ch, CURLOPT_NOBODY, true);
            curl_setopt($ch, CURLOPT_HTTPGET, true);
        }

        $status = curl_getinfo($ch);

        $return = array('exec' => curl_exec($ch), 'info' => $status, 'error' => curl_error($ch));

        //curl_close($ch);

        return $return;
    }

    public static function curlPost($url, $post, $cookie)
    {

        $new_url = $url;

        $ch = curl_init($new_url);

        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_REFERER, $new_url);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $agent = "Mozilla/5.0 (Windows; U; Windows NT 5.0; en-US; rv:1.4) Gecko/20030624 Netscape/7.1 (ax)";
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        //curl_setopt($ch, CURLOPT_POSTREDIR, 1);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $status = curl_getinfo($ch);

        $return = array('exec' => curl_exec($ch), 'info' => $status, 'error' => curl_error($ch));

        //curl_close($ch);

        return $return;
    }
}