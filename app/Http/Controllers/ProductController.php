<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Validator;
use App\Http\Requests;
use LaravelLocalization;
use Illuminate\Http\Request;

use App\Model\Unit;
use App\Model\Product;
use App\Model\ProductType;
use App\Model\ProductUnit;
use App\Model\ProductCategory;

use App\Repos\LookupRepo;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $product = Product::paginate(10);

        return view('product.index')->with('productlist', $product);
    }

    public function show($id)
    {
        $product = Product::find($id);

        return view('product.show')->with('product', $product);
    }

    public function create()
    {
        $statusDDL = LookupRepo::findByCategory('STATUS')->pluck('description', 'code');
        $prodtypeDdL = ProductType::get()->pluck('name', 'id');
        $unitDDL = Unit::whereStatus('STATUS.ACTIVE')->get()->pluck('unit_name', 'id');

        return view('product.create', compact('statusDDL', 'prodtypeDdL', 'unitDDL'));
    }

    public function store(Request $data)
    {
        $validator = Validator::make($data->all(), [
            'type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'status' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect(route('db.master.product.create'))->withInput()->withErrors($validator);
        } else {
            if (count($data['unit_id']) == 0) {
                $validator->getMessageBag()->add('unit', LaravelLocalization::getCurrentLocale() == "en" ? "Please provide at least 1 unit.":"Harap isi paling tidak 1 satuan");
                return redirect(route('db.master.product.create'))->withInput()->withErrors($validator);
            }

            DB::transaction(function() use ($data) {
                $product = new Product;
                $product->store_id = Auth::user()->store->id;
                $product->product_type_id = $data['type'];
                $product->name = $data['name'];
                $product->short_code = $data['short_code'];
                $product->barcode = $data['barcode'];
                $product->minimal_in_stock = $data['minimal_in_stock'];
                $product->description = $data['description'];
                $product->status = $data['status'];
                $product->remarks = $data['remarks'];

                $product->save();

                for ($i = 0; $i < count($data['unit_id']); $i++) {
                    $punit = new ProductUnit();
                    $punit->unit_id = $data['unit_id'][$i];
                    $punit->is_base = $data['is_base'][$i] == 'true' ? true:false;
                    $punit->conversion_value = $data['conversion_value'][$i];
                    $punit->remarks = empty($data['remarks'][$i]) ? '' : $data['remarks'][$i];

                    $product->productUnits()->save($punit);
                }

                for ($j = 0; $j < count($data['cat_level']); $j++) {
                    $pcat = new ProductCategory();
                    $pcat->store_id = Auth::user()->store->id;
                    $pcat->code = $data['cat_code'][$j];
                    $pcat->name = $data['cat_name'][$j];
                    $pcat->description = $data['cat_description'][$j];
                    $pcat->level = $data['cat_level'][$j];

                    $product->productCategories()->save($pcat);
                }
            });

            return redirect(route('db.master.product'));
        }
    }

    public function edit($id)
    {
        $product = Product::find($id);

        $statusDDL = LookupRepo::findByCategory('STATUS')->pluck('description', 'code');
        $prodtypeDdL = ProductType::get()->pluck('name', 'id');
        $unitDDL = Unit::whereStatus('STATUS.ACTIVE')->get()->pluck('unit_name', 'id');

        $selected = $product->type->id;

        return view('product.edit', compact('product', 'statusDDL', 'prodtypeDdL', 'selected', 'unitDDL'));
    }

    public function update($id, Request $data)
    {
        $validator = Validator::make($data->all(), [
            'type' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'short_code' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'status' => 'required|string|max:255',
        ]);

        if (count($data['unit_id']) == 0) {
            $validator->getMessageBag()->add('unit', LaravelLocalization::getCurrentLocale() == "en" ? "Please provide at least 1 unit.":"Harap isi paling tidak 1 satuan");
            return redirect(route('db.master.product.create'))->withInput()->withErrors($validator);
        }

        if ($validator->fails()) {
            return redirect(route('db.master.product.create'))->withInput()->withErrors($validator);
        }

        DB::transaction(function() use ($id, $data) {
            $product = Product::find($id);

            $product->productUnits->each(function($pu) { $pu->delete(); });

            $pu = array();
            for ($i = 0; $i < count($data['unit_id']); $i++) {
                $punit = new ProductUnit();
                $punit->unit_id = $data['unit_id'][$i];
                $punit->is_base = $data['is_base'][$i] == 'true' ? true:false;
                $punit->conversion_value = $data['conversion_value'][$i];
                $punit->remarks = empty($data['remarks'][$i]) ? '' : $data['remarks'][$i];

                array_push($pu, $punit);
            }

            $product->productUnits()->saveMany($pu);

            $product->productCategories->each(function($pc) { $pc->delete(); });

            $pclist = array();
            for ($j = 0; $j  < count($data['cat_level']); $j++) {
                $pcat = new ProductCategory();
                $pcat->store_id = Auth::user()->store->id;
                $pcat->code = $data['cat_code'][$j];
                $pcat->name = $data['cat_name'][$j];
                $pcat->description = $data['cat_description'][$j];
                $pcat->level = $data['cat_level'][$j];

                array_push($pclist, $pcat);
            }

            $product->productCategories()->saveMany($pclist);

            $product->update([
                'product_type_id' => $data['type'],
                'name' => $data['name'],
                'short_code' => $data['short_code'],
                'description' => $data['description'],
                'status' => $data['status'],
                'remarks' => $data['remarks'],
                'barcode' => $data['barcode'],
                'minimal_in_stock' => $data['minimal_in_stock'],
            ]);
        });

        return redirect(route('db.master.product'));
    }

    public function delete($id)
    {
        $product = Product::find($id);

        $product->productUnits->each(function($pu) { $pu->delete(); });
        $product->productCategories->each(function($pc) { $pc->delete(); });
        $product->delete();

        return redirect(route('db.master.product'));
    }
}
