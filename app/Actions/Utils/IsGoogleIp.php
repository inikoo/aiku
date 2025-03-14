<?php

/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Fri, 24 Nov 2023 04:33:18 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2023, Raul A Perusquia Flores
 */

namespace App\Actions\Utils;

use Lorisleiva\Actions\Concerns\AsAction;

class IsGoogleIp
{
    use AsAction;

    public function handle($ip): bool
    {
        if (!$ip) {
            return false;
        }

        foreach ($this->googleIpRanges() as $rangeData) {
            if (array_keys($rangeData)[0] == 'ipv4Prefix') {
                $range = array_values($rangeData)[0];

                $inRange = $this->ipInRange($ip, $range);
                if ($inRange) {
                    return true;
                }

            }
        }
        return false;
    }



    public function ipInRange($ip, $range): bool
    {
        list($range, $netmask) = explode('/', $range, 2);
        if (str_contains($netmask, '.')) {

            $netmask     = str_replace('*', '0', $netmask);
            $netmask_dec = ip2long($netmask);
            return ((ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec));
        } else {

            $x = explode('.', $range);
            while (count($x) < 4) {
                $x[] = '0';
            }
            list($a, $b, $c, $d) = $x;
            $range               = sprintf("%u.%u.%u.%u", empty($a) ? '0' : $a, empty($b) ? '0' : $b, empty($c) ? '0' : $c, empty($d) ? '0' : $d);
            $range_dec           = ip2long($range);
            $ip_dec              = ip2long($ip);


            $wildcard_dec = pow(2, (32 - $netmask)) - 1;
            $netmask_dec  = ~$wildcard_dec;

            return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
        }


    }


    protected function googleIpRanges(): array
    {
        return
            [
                  [
                      "ipv4Prefix" => "8.8.4.0/24"
                  ],
                  [
                      "ipv4Prefix" => "8.8.8.0/24"
                  ],
                  [
                      "ipv4Prefix" => "8.34.208.0/20"
                  ],
                  [
                      "ipv4Prefix" => "8.35.192.0/20"
                  ],
                  [
                      "ipv4Prefix" => "23.236.48.0/20"
                  ],
                  [
                      "ipv4Prefix" => "23.251.128.0/19"
                  ],
                  [
                      "ipv4Prefix" => "34.0.0.0/15"
                  ],
                  [
                      "ipv4Prefix" => "34.2.0.0/16"
                  ],
                  [
                      "ipv4Prefix" => "34.3.0.0/23"
                  ],
                  [
                      "ipv4Prefix" => "34.3.3.0/24"
                  ],
                  [
                      "ipv4Prefix" => "34.3.4.0/24"
                  ],
                  [
                      "ipv4Prefix" => "34.3.8.0/21"
                  ],
                  [
                      "ipv4Prefix" => "34.3.16.0/20"
                  ],
                  [
                      "ipv4Prefix" => "34.3.32.0/19"
                  ],
                  [
                      "ipv4Prefix" => "34.3.64.0/18"
                  ],
                  [
                      "ipv4Prefix" => "34.3.128.0/17"
                  ],
                  [
                      "ipv4Prefix" => "34.4.0.0/14"
                  ],
                  [
                      "ipv4Prefix" => "34.8.0.0/13"
                  ],
                  [
                      "ipv4Prefix" => "34.16.0.0/12"
                  ],
                  [
                      "ipv4Prefix" => "34.32.0.0/11"
                  ],
                  [
                      "ipv4Prefix" => "34.64.0.0/10"
                  ],
                  [
                      "ipv4Prefix" => "34.128.0.0/10"
                  ],
                  [
                      "ipv4Prefix" => "35.184.0.0/13"
                  ],
                  [
                      "ipv4Prefix" => "35.192.0.0/14"
                  ],
                  [
                      "ipv4Prefix" => "35.196.0.0/15"
                  ],
                  [
                      "ipv4Prefix" => "35.198.0.0/16"
                  ],
                  [
                      "ipv4Prefix" => "35.199.0.0/17"
                  ],
                  [
                      "ipv4Prefix" => "35.199.128.0/18"
                  ],
                  [
                      "ipv4Prefix" => "35.200.0.0/13"
                  ],
                  [
                      "ipv4Prefix" => "35.208.0.0/12"
                  ],
                  [
                      "ipv4Prefix" => "35.224.0.0/12"
                  ],
                  [
                      "ipv4Prefix" => "35.240.0.0/13"
                  ],
                  [
                      "ipv4Prefix" => "64.15.112.0/20"
                  ],
                  [
                      "ipv4Prefix" => "64.233.160.0/19"
                  ],
                  [
                      "ipv4Prefix" => "66.22.228.0/23"
                  ],
                  [
                      "ipv4Prefix" => "66.102.0.0/20"
                  ],
                  [
                      "ipv4Prefix" => "66.249.64.0/19"
                  ],
                  [
                      "ipv4Prefix" => "70.32.128.0/19"
                  ],
                  [
                      "ipv4Prefix" => "72.14.192.0/18"
                  ],
                  [
                      "ipv4Prefix" => "74.125.0.0/16"
                  ],
                  [
                      "ipv4Prefix" => "104.154.0.0/15"
                  ],
                  [
                      "ipv4Prefix" => "104.196.0.0/14"
                  ],
                  [
                      "ipv4Prefix" => "104.237.160.0/19"
                  ],
                  [
                      "ipv4Prefix" => "107.167.160.0/19"
                  ],
                  [
                      "ipv4Prefix" => "107.178.192.0/18"
                  ],
                  [
                      "ipv4Prefix" => "108.59.80.0/20"
                  ],
                  [
                      "ipv4Prefix" => "108.170.192.0/18"
                  ],
                  [
                      "ipv4Prefix" => "108.177.0.0/17"
                  ],
                  [
                      "ipv4Prefix" => "130.211.0.0/16"
                  ],
                  [
                      "ipv4Prefix" => "142.250.0.0/15"
                  ],
                  [
                      "ipv4Prefix" => "146.148.0.0/17"
                  ],
                  [
                      "ipv4Prefix" => "162.216.148.0/22"
                  ],
                  [
                      "ipv4Prefix" => "162.222.176.0/21"
                  ],
                  [
                      "ipv4Prefix" => "172.110.32.0/21"
                  ],
                  [
                      "ipv4Prefix" => "172.217.0.0/16"
                  ],
                  [
                      "ipv4Prefix" => "172.253.0.0/16"
                  ],
                  [
                      "ipv4Prefix" => "173.194.0.0/16"
                  ],
                  [
                      "ipv4Prefix" => "173.255.112.0/20"
                  ],
                  [
                      "ipv4Prefix" => "192.158.28.0/22"
                  ],
                  [
                      "ipv4Prefix" => "192.178.0.0/15"
                  ],
                  [
                      "ipv4Prefix" => "193.186.4.0/24"
                  ],
                  [
                      "ipv4Prefix" => "199.36.154.0/23"
                  ],
                  [
                      "ipv4Prefix" => "199.36.156.0/24"
                  ],
                  [
                      "ipv4Prefix" => "199.192.112.0/22"
                  ],
                  [
                      "ipv4Prefix" => "199.223.232.0/21"
                  ],
                  [
                      "ipv4Prefix" => "207.223.160.0/20"
                  ],
                  [
                      "ipv4Prefix" => "208.65.152.0/22"
                  ],
                  [
                      "ipv4Prefix" => "208.68.108.0/22"
                  ],
                  [
                      "ipv4Prefix" => "208.81.188.0/22"
                  ],
                  [
                      "ipv4Prefix" => "208.117.224.0/19"
                  ],
                  [
                      "ipv4Prefix" => "209.85.128.0/17"
                  ],
                  [
                      "ipv4Prefix" => "216.58.192.0/19"
                  ],
                  [
                      "ipv4Prefix" => "216.73.80.0/20"
                  ],
                  [
                      "ipv4Prefix" => "216.239.32.0/19"
                  ],
                  [
                      "ipv6Prefix" => "2001:4860::/32"
                  ],
                  [
                      "ipv6Prefix" => "2404:6800::/32"
                  ],
                  [
                      "ipv6Prefix" => "2404:f340::/32"
                  ],
                  [
                      "ipv6Prefix" => "2600:1900::/28"
                  ],
                  [
                      "ipv6Prefix" => "2606:73c0::/32"
                  ],
                  [
                      "ipv6Prefix" => "2607:f8b0::/32"
                  ],
                  [
                      "ipv6Prefix" => "2620:11a:a000::/40"
                  ],
                  [
                      "ipv6Prefix" => "2620:120:e000::/40"
                  ],
                  [
                      "ipv6Prefix" => "2800:3f0::/32"
                  ],
                  [
                      "ipv6Prefix" => "2a00:1450::/32"
                  ],
                  [
                      "ipv6Prefix" => "2c0f:fb50::/32"
                  ]
              ];

    }



}
