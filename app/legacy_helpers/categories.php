<?php
/*
 * Author: Raul A Perusquía-Flores (raul@aiku.io)
 * Created: Fri, 20 Nov 2020 14:51:14 Malaysia Time, Kuala Lumpur, Malaysia
 * Copyright (c) 2020. Aiku.io
 */


use App\Models\Helpers\Category;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

function relocate_product_categories($store, $legacy_data) {
    $sql = "* from `Category Dimension` where `Category Key` not in (?,?) and `Category Scope`='Product'  and `Category Branch Type`='Root' and `Category Store Key`=? ";
    foreach (
        DB::connection('legacy')->select(
            "select ".$sql, [
                              $legacy_data->{'Store Department Category Key'},
                              $legacy_data->{'Store Family Category Key'},
                              $legacy_data->{'Store Key'}

                          ]
        ) as $products_category_data
    ) {


        $type = $products_category_data->{'Category Subject'};


        $root = (new Category)->updateOrCreate(
            [
                'type'         => $type,
                'legacy_id'    => $products_category_data->{'Category Key'},
                'container'    => 'Store',
                'container_id' => $store->id,
            ], [
                'code'      => $products_category_data->{'Category Code'},
                'name'      => $products_category_data->{'Category Label'},
                'tenant_id' => $store->tenant_id,

            ]
        );

        $sql = "* from `Category Dimension` where `Category Root Key`=?  and `Category Branch Type`!='Root'  ";
        foreach (DB::connection('legacy')->select("select ".$sql, [$products_category_data->{'Category Key'}]) as $category_legacy_data) {


            $root->children()->updateOrCreate(
                [
                    'type'         => $type,
                    'legacy_id'    => $category_legacy_data->{'Category Key'},
                    'container'    => 'Store',
                    'container_id' => $store->id,
                ], [
                    'code'      => $category_legacy_data->{'Category Code'},
                    'name'      => $category_legacy_data->{'Category Label'},
                    'tenant_id' => $store->tenant_id,
                ]
            );


        }


    }
}

function relocate_product_hierarchy($tenant,$store, $legacy_data) {

    $sql = "* from `Category Dimension` where `Category Key`=? ";
    foreach (DB::connection('legacy')->select("select ".$sql, [$legacy_data->{'Store Department Category Key'}]) as $departments_legacy_data) {


        $root = (new Category)->updateOrCreate(
            [
                'type'         => 'Product',
                'legacy_id'    => $departments_legacy_data->{'Category Key'},
                'container'    => 'Store',
                'container_id' => $store->id,
            ], [
                'code'      => $departments_legacy_data->{'Category Code'},
                'name'      => $departments_legacy_data->{'Category Label'},
                'tenant_id' => $store->tenant_id,

            ]
        );


        $legacyData = $tenant->data;
        Arr::set($legacyData, 'categories.products.hierarchy', $root->id);
        $tenant->data = $legacyData;
        $tenant->save();

        $sql = "* from `Category Dimension` where `Category Root Key`=?  and `Category Branch Type`!='Root'  ";
        foreach (DB::connection('legacy')->select("select ".$sql, [$departments_legacy_data->{'Category Key'}]) as $department_legacy_data) {


            $department = $root->children()->updateOrCreate(
                [
                    'type'         => 'Product',
                    'legacy_id'    => $department_legacy_data->{'Category Key'},
                    'container'    => 'Store',
                    'container_id' => $store->id,
                ], [
                    'code'      => $department_legacy_data->{'Category Code'},
                    'name'      => $department_legacy_data->{'Category Label'},
                    'tenant_id' => $tenant->id
                ]
            );


            $sql = "C.`Category Key`,`Category Code`,`Category Label`,`Subject`, B.`Category Key`,`Subject Key`
                         from   `Category Dimension` C  left join `Category Bridge`B  on (`Subject Key`=C.`Category Key`)  where  `Subject`='Category'  and B.`Category Key`=?  ";
            foreach (DB::connection('legacy')->select("select ".$sql, [$department_legacy_data->{'Category Key'}]) as $family_legacy_data) {


                $family = $department->children()->updateOrCreate(
                    [
                        'type'         => 'Product',
                        'legacy_id'    => $family_legacy_data->{'Subject Key'},
                        'container'    => 'Store',
                        'container_id' => $store->id,
                        'parent_id'    => $department->id,
                    ], [
                        'code'      => $family_legacy_data->{'Category Code'},
                        'name'      => $family_legacy_data->{'Category Label'},
                        'tenant_id' => $tenant->id
                    ]
                );

                $sql = "C.`Category Key` from `Category Bridge` B  left join `Category Dimension` C on (B.`Category Key`=C.`Category Key`) where `Category Branch Type`='Head' and `Subject`='Product' and `Subject Key`=?";
                foreach (DB::connection('legacy')->select("select $sql", [$family_legacy_data->{'Subject Key'}]) as $legacy_category_data) {
                    $category = Category::firstWhere('legacy_id', $legacy_category_data->{'Category Key'});
                    if ($category) {
                        $category->families()->syncWithoutDetaching([$family->id]);
                    }
                }


            }


        }


    }


}

function relocate_category($tenant,$type, $legacy_data, $container) {
    $root = (new Category)->updateOrCreate(
        [
            'type'         => $type,
            'legacy_id'    => $legacy_data->{'Category Key'},
            'container'    => $container[0],
            'container_id' => $container[1],
        ], [
            'code'      => $legacy_data->{'Category Code'},
            'name'      => $legacy_data->{'Category Label'},
            'tenant_id' => $tenant->id,


        ]
    );


    $sql = "* from `Category Dimension` where `Category Root Key`=?  and `Category Branch Type`!='Root'  ";
    foreach (DB::connection('legacy')->select("select ".$sql, [$legacy_data->{'Category Key'}]) as $category_legacy_data) {

        $root->children()->updateOrCreate(
            [
                'type'         => $type,
                'legacy_id'    => $category_legacy_data->{'Category Key'},
                'container'    => $container[0],
                'container_id' => $container[1],
            ], [
                'code'      => $category_legacy_data->{'Category Code'},
                'name'      => $category_legacy_data->{'Category Label'},
                'tenant_id' => $tenant->id,

            ]
        );


    }
    return $root;
}
