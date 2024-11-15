<?php
/*
 * Author: Raul Perusquia <raul@inikoo.com>
 * Created: Sun, 09 Jun 2024 12:55:09 Central European Summer Time, Plane Abu Dhabi - Kuala Lumpur
 * Copyright (c) 2024, Raul A Perusquia Flores
 */

namespace App\Transfers\Aurora;

use Illuminate\Support\Facades\DB;

class FetchAuroraIngredient extends FetchAurora
{
    protected function parseModel(): void
    {
        $prefix = '';
        $suffix = '';
        $name   = $this->auroraModelData->{'Material Name'};
        $name   = preg_replace('/\s+/', ' ', $name);
        $name   = trim($name);
        $name   = preg_replace('/^\p{Z}+|\p{Z}+$/u', '', $name);


        if (is_numeric($name) or $name == '' or strlen($name) == 1 or
            in_array(
                $name,
                [
                    '2 sheets of 18 Front Labels and 2 sheets of 18 Back (Ingredients) Labels',
                    '2 sheets of 30 Front Labels and 2 sheets of 30 Back (Ingredients) Labels',
                    '36x Front Label 36x Back Label',
                    'Bath',
                    'Bath Fizzes',
                    'Bath Bombs',
                    'A3 Printed Poster',
                    '*Occur naturally in Essential Oils',
                    '*Naturally occurring in Essential Oils',
                    'Gift Box',
                    'H: 2 cm L: 2.8 cm l: 4 cm environ',
                    'Label',
                    '5%)',
                    '37x40',
                    '21x',
                    'xx',
                    'www.ancient-wisdom.info',
                    '24hrs Burning time',
                    '30 hours burning time',
                    'Temps de combustion: 30h'
                ]
            )) {
            return;
        }

        //print "Orifinal>>$name<<<";

        $name = preg_replace('/^\.\*/', '', $name);

        if (preg_match('/^\*\s*/', $name)) {
            $name   = preg_replace('/^\*\s*/', '', $name);
            $prefix = '*';
        }
        if (preg_match('/\s*\*$/', $name)) {
            $name   = preg_replace('/\s*\*$/', '', $name);
            $prefix = '*';
        }
        if (preg_match('/^±\s*/', $name)) {
            $name   = preg_replace('/^±\s*/', '', $name);
            $prefix = '±';
        }
        if (preg_match('|^\(\+/- \s*|', $name)) {
            $name   = preg_replace('|^\(\+/- \s*|', '', $name);
            $prefix = '±';
        }
        if (preg_match('|^\(\+/- \s*|', $name)) {
            $name   = preg_replace('|^\(\+/- \s*|', '', $name);
            $prefix = '±';
        }
        $name = preg_replace('/^"\s*/', '', $name);
        $name = preg_replace('/^~\s*/', '', $name);
        $name = preg_replace('/^\s*-\s*/', '', $name);
        $name = preg_replace('/^\.\s*/', '', $name);


        $name = preg_replace('/\s*\.*\s*\*?\s*(Occur naturally|Naturally occurring) in Essential Oils?$/', '', $name);

        $name = preg_replace('/\s*\*\s*Occurs naturally in Essential Oils$/', '', $name);

        $name = preg_replace('/^Ingredients\s*([:\-])\s*/', '', $name);


        $name = preg_replace('/\(\+\/Cl |\(\+\/_CI /', 'CI ', $name);

        if ($name == '(+/-Titanium Dioxide)') {
            $name   = 'Titanium Dioxide';
            $suffix = '±';
        }
        if ($name == '30 hours burning time. Wax') {
            $name = 'Wax';
        }
        if ($name == 'Acrilic Paint') {
            $name = 'Acrylic Paint';
        }
        if ($name == 'Terracota') {
            $name = 'Terracotta';
        }

        if ($name == 'Eugenol)') {
            $name = 'Eugenol';
        }

        if ($name == 'Coumarin)') {
            $name = 'Coumarin';
        }

        if ($name == 'Coconut) Oil)') {
            $name = 'Coconut Oil';
        }

        if ($name == 'Brass Metal') {
            $name = 'Brass';
        }


        if ($name == 'Citric A') {
            $name = 'Citric Acid';
        }


        if (in_array(
            $name,
            [
                'Alpah-Isomethyl Ionone',
                'Alph-Isomethyl Ionone',
                'Alpha Isomethyl Ionone',
                'Alpha isomethly ionone)',
                'Alpha-Iso-methylionone',
                'Alpha-Isomethyl Ionone',
                'Alpha-Isomethyl Ionone)',
                'Alpha-Isonethyl Ionone',
                'Alphaisomethyl Ionone',
                'Alpho-Isomethyl Ionone'
            ]
        )) {
            $name = 'α-Isomethyl Ionone';
        }


        if ($name == 'Glasses') {
            $name = 'Glass';
        }


        if ($name == 'Incense stick') {
            $name = 'Incense Sticks';
        }


        if ($name == 'Stainless' or $name == 'Stainless Steal') {
            $name = 'Stainless Steel';
        }

        if ($name == 'Teak' or $name == 'Teakwood' or $name == 'Teakwood wood') {
            $name = 'Teak Wood';
        }

        if ($name == 'Soy-bean Wax' or $name == 'Soywax') {
            $name = 'Soy Wax';
        }

        if ($name == 'Plastik') {
            $name = 'Plastic';
        }
        if ($name == 'Ruber') {
            $name = 'Rubber';
        }

        if ($name == 'Salt-50') {
            $name = 'Salt';
        }
        if ($name == 'Soaps') {
            $name = 'Soap';
        }

        if ($name=="Product Ingredients Lavender: Sodium Bicarbonate" or $name="Ingredients Sodium Bicarbonate" or $name == 'Ingredients / Ingrédients / Bestandteile / Ingredientes / Skład / Ingredienti : Sodium Bicarbonate') {
            $name = 'Sodium Bicarbonate';
        }

        if ($name == 'CI 18050.') {
            $name = 'CI 18050';
        }
        if ($name == 'C.I. 14.720') {
            $name = 'CI 14720';
        }
        if ($name == 'C.I. 17.200') {
            $name = 'CI 17200';
        }
        if ($name == 'C.I. 50.420') {
            $name = 'CI 50420';
        }
        if ($name == 'C.I. 42.045') {
            $name = 'CI 42045';
        }

        if ($name == 'C16255') {
            $name = 'CI 16255';
        }
        if ($name == 'C.I. 77.891') {
            $name = 'CI 77891';
        }

        if ($name == 'Chlorophyl') {
            $name = 'Chlorophyll';
        }


        if ($name == 'Sucrosa') {
            $name = 'Sucrose';
        }

        if ($name == 'WATER') {
            $name = 'Water';
        }


        if ($name == 'Adam: Sodium Bicarbonate') {
            $name = 'Sodium Bicarbonate';
        }


        if (in_array($name, ['Acrilic', 'Acrillic'])) {
            $name = 'Acrylic';
        }

        if (in_array($name, ['Albesia', 'Albacia wood', 'Albasia Wood', 'Albesia Wood', 'Albesia Wood Stand', 'Albesian Wood', 'Albacia wood', 'Albasia Wood'])) {
            $name = 'Albasia Wood';
        }

        if (in_array($name, ['6-Trimetyl-2- Cyclohexen-1-Yl)-3-Buten-2-One', '6-Trimetyl-2-Cyclohexen-1-Yl) -3-Buten-2-One', '6-Trimetyl-2-Cyclohexen-1-Yl)-3- Buten-2-One'])) {
            $name = '6-Trimetyl-2-Cyclohexen-1-Yl)-3-Buten-2-One';
        }


        if (in_array($name, ['. *Naturally occurring in Essential Oils', '. *Occur naturally in Essential Oils', '. *Occur naturally in Essential Oils. Do not use internally. Keep out of reach of children'])) {
            return;
        }

        if (preg_match('/^\(.+\)/', $name)) {
            $name = preg_replace('/^\(\s*/', '', $name);
            $name = preg_replace('/^\s*\)/', '', $name);
        }

        if (preg_match('/^CI(\d+)$/', $name, $matches)) {
            $name = 'CI '.$matches[1];
        }
        if (preg_match('/^C\.I\.\s+(\d+)$/', $name, $matches)) {
            $name = 'CI '.$matches[1];
        }

        if (preg_match('/^Cl\s*(\d+)$/', $name, $matches)) {
            $name = 'CI '.$matches[1];
        }

        if ($name == '') {
            return;
        }

        //print ">>$name<<<\n";


        $this->parsedData['metadata']   = [
            'prefix'  => $prefix,
            'suffix'  => $suffix,
            'wrapper' => '',
        ];
        $this->parsedData['ingredient'] = [
            'name'            => $name,
            'source_id'       => $this->organisation->id.':'.$this->auroraModelData->{'Material Key'},
            'fetched_at'      => now(),
            'last_fetched_at' => now(),
        ];
    }


    protected function fetchData($id): object|null
    {
        return DB::connection('aurora')
            ->table('Material Dimension')
            ->where('Material Key', $id)->first();
    }
}
