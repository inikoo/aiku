<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Wed, 26 Aug 2020 23:46:40 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */

namespace App\Models\Helpers;

use Illuminate\Database\Eloquent\Model;
use Spatie\Multitenancy\Models\Concerns\UsesLandLordConnection;

class IpGeolocation extends Model {
    use UsesLandLordConnection;

    protected $fillable = ['ip'];
    protected $casts = [
        'data' => 'array'
    ];

    protected static function booted() {
        static::created(
            function ($ipGeolocation) {
                $ipGeolocation->fetch_ip_geolocation_data();
            }
        );
    }

    public function fetch_ip_geolocation_data() {


        $api_url=config('app.geolocation_api.url');

        $api_keys = preg_split('/,/', config('app.geolocation_api.keys'));

        if (count($api_keys) == 0 or $api_url=='') {
            return;
        }
        shuffle($api_keys);

        $access_credentials = preg_split('/\|/', reset($api_keys))[1];

        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => false,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS      => 10,
            CURLOPT_ENCODING       => "",
            CURLOPT_AUTOREFERER    => true,
            CURLOPT_CONNECTTIMEOUT => 120,
            CURLOPT_TIMEOUT        => 120,
        );

        $ch = curl_init($api_url.$this->ip.'?access_key='.$access_credentials);

        curl_setopt_array($ch, $options);

        $data = json_decode(curl_exec($ch), true);


        $this->geoname_id     = $data['location']['geoname_id'];
        $this->continent_code = $data['continent_code'];
        $this->country_code   = $data['country_code'];

        unset($data['continent_code']);
        unset($data['continent_name']);

        unset($data['country_code']);
        unset($data['country_name']);

        unset($data['ip']);
        unset($data['location']['geoname_id']);

        $this->data = $data;
        $this->status = 'OK';

        curl_close($ch);


        $this->geolocation_label = $this->get_geolocation_label();
        $this->save();


    }

    public function get_geolocation_label() {

        $label = '';

        if (!empty($this->data['location']['country_flag_emoji'])) {
            $label = $this->data['location']['country_flag_emoji'];
        }

        if (!empty($this->data['city'])) {
            $label .= ' '.$this->data['city'];
        } elseif (!empty($this->data['region_name'])) {
            $label .= ' '.$this->data['region_name'];
        }

        $label = trim($label);

        return $label;



    }

}
