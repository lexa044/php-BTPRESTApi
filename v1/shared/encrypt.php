<?php
  class encrypt {

    function getSessionToken( $string, $action = 'e' ) {
        $secret_key = 'BPkNp1_j1OdITmmFv9vsFNEb5E9XJaQbTfIws0LtXt4JsRIXooyIe0PdChDDF8iVn1YhjKmHFumZZGydP9pnfazOA-znZabA80PH2HZUBw4kpaKmG36Y5bCotnOLaf4CytSjId3HnBxK5v975z46QBur5DSiA7jzJrDy57dDYMyy8oBJ4SZzKOsllU2N7JV87J3H7SiioxrSksF2ztFksSnSxf_qeOTB50SYw3TR9_LN5E6IIsXAghCqJwYZTnXEnttJjbOpDkbKjGkw7FB20zhagrbi5INJYLPrOb2NDC9WgL7_9M2OZUuGhzzx9X--Pe9aTQITIGDgjQOva1VB9DIeg9GxfZS25Hgx8AapjhfZ0EEj-FAa7S5HRGnxCy6T61OgnmCLHBvGOTLTiVhxo0abLlACsg6pdqppVEx32EVlqgVJ';

        $secret_iv = 'odIQ1TukErMf_jLdWO931O8Oq8FkirWiMwi5w_TlBsnsDrejCW1yHozgd7ZsXgVDW74D6gUNh5ZIdLAiH_zdsLiGUjrknWGydxFtmZI9MFLWiK5czwZnwisDs0IUiHF7Z2Uszor988j2gPBaVzbY5IjFQmsOBvyNTQqgPk4vSwE9KluRV793qFU6Rg4jWq6WQe7IMOG4yQULIdDp6y3znSx8oS0tzxqlqGv_pGWE4gGS7k_5tQUWXGlrB-tBy77hMbxpuFCusgODvRSc6TZi0FkskKoKbaMqTwD_wvPgqsu-B4Cs9CNY0Ns3uWFNp-ZppTkw-e5Mf3yynuxdyCdoOLcUJPjkF72kpnhDJPi79JWaw9N6gZoZHofDk7BJXyQlsJDD_8Y3GyGasnPFGJIHXiEAb_zhwJiPqqAZ6uBiujtn0CJN';

        $output = false;
        $encrypt_method = "AES-256-CBC";
        $key = hash( 'sha256', $secret_key );
        $iv = substr( hash( 'sha256', $secret_iv ), 0, 16 );

        if( $action == 'e' ) {
            $output = base64_encode( openssl_encrypt( $string, $encrypt_method, $key, 0, $iv ) );
        }
        else if( $action == 'd' ){
            $output = openssl_decrypt( base64_decode( $string ), $encrypt_method, $key, 0, $iv );
        }

        return $output;
    }
}