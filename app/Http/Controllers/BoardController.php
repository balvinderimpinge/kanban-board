<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Card;
use App\Models\Column;
use App\Models\Order;

class BoardController extends Controller
{
    /**
     * Show the dashboard
     */
    public function index() {
        try {
            $res = Column::with(['cards' => function($q) {
                $q->join('orders','orders.card_id','=','cards.id')->orderBy('orders.order','desc');
            }])->get();

            return response()->json([
                'status' => 'success',
                'message' => $res
            ],200);
        } catch(\Exception $e) {
            return response()->json([
                'result' => "error",
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Store column
     */
    public function storeColumn(Request $request) {
        $validator = Validator::make($request->all(), [
            'column_name' => 'required|string|max:100'
        ]);

        if($validator->fails()) {
            return response()->json([
                'result' => "error",
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            Column::create([
                'name' => $request->input('column_name')
            ]);

            return response()->json(["result" => "success"], 200);
        } catch(\Exception $e) {
            return response()->json([
                'result' => "error",
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Store Card
     */
    public function storeCard(Request $request) {
        $validator = Validator::make($request->all(), [
            'card_name' => 'required|string|max:100',
            'description' => 'required|string|max:1500',
            'column_id' => 'required|integer',
        ]);

        if($validator->fails()) {
            return response()->json([
                'result' => "error",
                'message' => $validator->errors()->first()
            ], 422);
        }

        DB::beginTransaction();
        try {
            $card = Card::create([
                'column_id' => $request->input('column_id'),
                'title' => $request->input('card_name'),
                'description' => $request->input('description')
            ]);

            Order::create([
                'column_id' => $request->input('column_id'),
                'card_id'   => $card->id,
                'order'     => Order::where('column_id', $request->input('column_id'))->orderBy('order','desc')->value('order') + 1
            ]);

            DB::commit();
            return response()->json(["result" => "success"], 200);
        } catch(\Exception $e) {
            DB::rollback();
            return response()->json([
                'result' => "error",
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get card information
     */
    public function getCardInfo($id) {
        try {
            $card = Card::find($id);
            
            return response()->json(['result'=>"success","title"=>$card->title,'description'=>$card->description], 200);
        } catch(\Exception $e) {
            return response()->json([
                'result' => "error",
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update Card
     */
    public function updateCard(Request $request) {
        $validator = Validator::make($request->all(), [
            'card_title' => 'required|string|max:100',
            'card_description' => 'required|string|max:1500',
        ]);

        if($validator->fails()) {
            return response()->json([
                'result' => "error",
                'message' => $validator->errors()->first()
            ], 422);
        }

        try {
            $card = Card::find($request->input('card_id'));
            $card->title = $request->input('card_title');
            $card->description = $request->input('card_description');
            $card->update();

            return response()->json(["result" => "success"], 200);
        } catch(\Exception $e) {
            return response()->json([
                'result' => "error",
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Move card
     */
    public function moveCard(Request $request) {
        DB::beginTransaction();
        try {
            $column_id = $request->input('current_column_id');
            if($request->input('direction') == 4) { // right
                $column_id = Column::where('id', '>', $request->input('current_column_id'))->min('id'); // get next id
            } elseif($request->input('direction') == 1) { // left
                $column_id = Column::where('id', '<', $request->input('current_column_id'))->max('id'); // get previous id
            } elseif($request->input('direction') == 2) { // up
                $current_order = Order::where('card_id', $request->input('card_id'))->value('order');
                $updated_order = $current_order+1;
                if($updated_order > 0) {
                    $updated = Order::where('column_id',$request->input('current_column_id'))->where('order',$updated_order)->update(['order'=>$current_order]);
                    if($updated > 0) {
                        Order::where('column_id',$request->input('current_column_id'))->where('card_id',$request->input('card_id'))->update(['order'=>$updated_order]);
                    }
                }
            } elseif($request->input('direction') == 3) { // down
                $current_order = Order::where('card_id', $request->input('card_id'))->value('order');
                $updated_order = $current_order-1;
                
                $updated = Order::where('column_id',$request->input('current_column_id'))->where('order',$updated_order)->update(['order'=>$current_order]);
                if($updated > 0) {
                    Order::where('column_id',$request->input('current_column_id'))->where('card_id',$request->input('card_id'))->update(['order'=>$updated_order]);
                }
            }


            if(!empty($column_id) && $column_id != $request->input('current_column_id')) {
                $card = Card::find($request->input('card_id'));
                $card->column_id = $column_id;
                $card->update();

                $order = Order::where('column_id', $column_id)->orderBy('order','desc')->value('order') + 1;
                Order::where('card_id',$request->input('card_id'))->update(['column_id'=>$column_id,'order'=>$order]);
            }           

            DB::commit();
            return response()->json(['result' => "success"], 200);
        } catch(\Exception $e) {
            DB::rollback();

            return response()->json([
                'result' => "error",
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete Column
     */
    public function deleteColumn($id) {
        try {
            $column = Column::find($id);
            $column->delete();

            return response()->json(['result' => "success"], 200);
        } catch(\Exception $e) {
            return response()->json([
                'result' => "error",
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Export DB
     */
    public function exportDB() {
        $pathToFile = public_path('dump.sql');
        
        \Spatie\DbDumper\Databases\MySql::create()
        ->setDbName(env('DB_DATABASE'))
        ->setUserName(env('DB_USERNAME'))
        ->setPassword(env('DB_PASSWORD'))
        ->dumpToFile($pathToFile);

        return response()->download($pathToFile);
    }
}
