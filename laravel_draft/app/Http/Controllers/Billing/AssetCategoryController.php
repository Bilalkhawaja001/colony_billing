<?php
namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AssetCategoryController extends Controller
{
    public function index()
    {
        $categories = DB::table('asset_categories')->orderBy('name')->get();
        $items = DB::table('asset_category_items')->orderBy('asset_name')->get()->groupBy('category_id');

        $assets = DB::table('asset_master')->where('is_active',1)->orderBy('asset_name')->get();

        return view('billing_control.asset-categories', [
            'assets'     => $assets,
            'pageTitle'  => 'Asset Categories',
            'categories' => $categories,
            'items'      => $items,
        ]);
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:120',
            'residence_type' => 'required|in:ROOM,HOUSE',
            'description'    => 'nullable|string|max:255',
        ]);
        DB::table('asset_categories')->insert($data + [
            'is_active' => 1, 'created_at' => now(), 'updated_at' => now(),
        ]);
        return back()->with('status', 'Category created: '.$data['name']);
    }

    public function updateCategory(Request $request, int $id)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:120',
            'residence_type' => 'required|in:ROOM,HOUSE',
            'description'    => 'nullable|string|max:255',
        ]);
        DB::table('asset_categories')->where('id', $id)->update($data + ['updated_at' => now()]);
        return back()->with('status', 'Category updated.');
    }

    public function deleteCategory(int $id)
    {
        DB::table('asset_category_items')->where('category_id', $id)->delete();
        DB::table('asset_categories')->where('id', $id)->delete();
        return back()->with('status', 'Category deleted.');
    }

    public function storeItem(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|integer',
            'asset_name'  => 'required|string|max:120',
            'quantity'    => 'required|integer|min:1',
        ]);
        DB::table('asset_category_items')->updateOrInsert(
            ['category_id' => $data['category_id'], 'asset_name' => $data['asset_name']],
            ['quantity' => $data['quantity'], 'updated_at' => now(), 'created_at' => now()]
        );
        return back()->with('status', 'Asset added: '.$data['asset_name']);
    }

    public function storeAsset(Request $request)
    {
        $data = $request->validate(['asset_name' => 'required|string|max:120']);
        DB::table('asset_master')->updateOrInsert(
            ['asset_name' => $data['asset_name']],
            ['is_active' => 1, 'updated_at' => now(), 'created_at' => now()]
        );
        return back()->with('status', 'Asset added to master: '.$data['asset_name']);
    }

    public function deleteAsset(int $id)
    {
        DB::table('asset_master')->where('id', $id)->delete();
        return back()->with('status', 'Asset removed from master.');
    }

    public function deleteItem(int $id)
    {
        DB::table('asset_category_items')->where('id', $id)->delete();
        return back()->with('status', 'Asset removed.');
    }
}
