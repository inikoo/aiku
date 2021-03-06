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


        $sql = "* from `Category Dimension` where `Category Root Key`=?  and `Category Branch Type`!='Root'   ";
        foreach (DB::connection('legacy')->select("select ".$sql, [$products_category_data->{'Category Key'}]) as $category_legacy_data) {


            $root->children()->withTrashed()->updateOrCreate(
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

function relocate_product_hierarchy($tenant, $store, $legacy_data) {

    //print_r($legacy_data->{'Store Family Category Key'});
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
        Arr::set($legacyData, 'categories.products.hierarchy.root', $root->id);
        $tenant->data = $legacyData;
        $tenant->save();

        $legacy_departments = [];
        $sql                = "* from `Category Dimension` where `Category Root Key`=?  and `Category Branch Type`!='Root'  ";
        foreach (DB::connection('legacy')->select("select ".$sql, [$departments_legacy_data->{'Category Key'}]) as $department_legacy_data) {

            $legacy_departments[] = $department_legacy_data->{'Category Key'};
            $department           = $root->children()->withTrashed()->updateOrCreate(
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


            $sql = "C.`Category Key`,`Category Code`,`Category Label`,`Subject`,`Subject Key` as `Legacy Key`
                         from   `Category Dimension` C  left join `Category Bridge`B  on (`Subject Key`=C.`Category Key`)  where  `Subject`='Category'  and B.`Category Key`=?  ";
            foreach (DB::connection('legacy')->select("select ".$sql, [$department_legacy_data->{'Category Key'}]) as $family_legacy_data) {


                relocate_add_family_to_department($department, $family_legacy_data);


            }


        }


        $uncategorizedCategory = $root->children()->withTrashed()->updateOrCreate(
            [
                'type'         => 'Product',
                'legacy_id'    => null,
                'container'    => 'Store',
                'container_id' => $store->id,
                'code'         => 'Uncategorized',
                'name'         => 'Uncategorized',
            ], [

                'tenant_id' => $tenant->id
            ]
        );
        $legacyData            = $tenant->data;
        Arr::set($legacyData, 'categories.products.hierarchy.uncategorized', $uncategorizedCategory->id);
        $tenant->data = $legacyData;
        $tenant->save();


        if (count($legacy_departments) > 0) {

            $sql = "C.`Category Key` as `Legacy Key` ,`Category Code`,`Category Label`
                         from   `Category Dimension` C where `Category Root Key`=?  and `Category Branch Type`!='Root' and (select count(*) from  `Category Bridge` B where  `Subject Key`=C.`Category Key` and `Subject`='Category' and `Category Key`=`Category Head Key`  and `Category Key` in ("
                .join(',', $legacy_departments).")  )=0 ";


            foreach (DB::connection('legacy')->select("select ".$sql, [$legacy_data->{'Store Family Category Key'}]) as $family_legacy_data) {


                relocate_add_family_to_department($uncategorizedCategory, $family_legacy_data);

            }

        }


    }


}


function relocate_add_family_to_department($department, $family_legacy_data) {


    $category = (new Category)->firstWhere('legacy_id', $family_legacy_data->{'Legacy Key'});
    if ($category) {
        $category->fill(
            [
                'parent_id' => $department->id,
                'code'      => $family_legacy_data->{'Category Code'},
                'name'      => $family_legacy_data->{'Category Label'},
            ]
        )->save();
    } else {
        $department->children()->withTrashed()->updateOrCreate(
            [
                'type'         => 'Product',
                'legacy_id'    => $family_legacy_data->{'Legacy Key'},
                'container'    => 'Store',
                'container_id' => $department->container_id,
                'tenant_id'    => $department->tenant_id

            ], [
                'parent_id' => $department->id,
                'code'      => $family_legacy_data->{'Category Code'},
                'name'      => $family_legacy_data->{'Category Label'},
            ]
        );
    }


}


function relocate_category($tenant, $type, $legacy_data, $container) {
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

        $root->children()->withTrashed()->updateOrCreate(
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

function relocate_deleted_categories($tenant, $legacy_data) {


    if ($legacy_data->{'Category Deleted Branch Type'} == 'Head') {
        if (preg_match('/<a href=\'category.php\?id=(\d+)/', $legacy_data->{'Category Deleted XHTML Branch Tree'}, $matches)) {

            $category = Category::withTrashed()->firstWhere('legacy_id', $matches[1]);


            if ($category) {


                $category->children()->withTrashed()->updateOrCreate(
                    [
                        'type'         => $category->type,
                        'legacy_id'    => $legacy_data->{'Category Deleted Key'},
                        'container'    => $category->container,
                        'container_id' => $category->container_id,
                    ], [
                        'code'       => $legacy_data->{'Category Deleted Code'},
                        'name'       => $legacy_data->{'Category Deleted Label'},
                        'deleted_at' => $legacy_data->{'Category Deleted Date'},
                        'tenant_id'  => $tenant->id,
                    ]
                );

            }
        }


    }


}
