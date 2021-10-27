<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit');
        $status = $request->input('status');
        
        if ($id) {
            $transaction = Transaction::find($id);
            if ($transaction) {
                return ResponseFormatter::success(
                    $transaction,
                    'Data transaksi berhasil diambil'
                );
            }else{
                return ResponseFormatter::error(
                    null,
                    'Data transaksi tidak ada',
                    404
                );
            }
        }

        $transactions = Transaction::with('transaction_items.product')->where('users_id', Auth::user()->id);
        if ($status) {
            $transactions->where('status', $status);
        }

        return ResponseFormatter::success(
            $transactions->paginate($limit),
            'data berhasil diambil'
        );
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'address' => 'required',
            'items' => 'required|array',
            'items.*.id' => 'exists:products,id',
            'total_price' => 'required',
            'shipping_price' => 'required',
            'status' => 'required|in:pending,success,canceled,failed,shipping,shipped'
        ]);

        try {
            $transaction = new Transaction();
            DB::transaction(function()use($request, $transaction) {
                $userId = Auth::user()->id;

                $transaction->users_id = $userId;
                $transaction->address = $request->address;
                $transaction->total_price = $request->shipping_price;
                $transaction->status = $request->status;
                $transaction->save();

                foreach ($request->items as $item) {
                    $transactionItem = new TransactionItem();
                    $transactionItem->users_id = $userId;
                    $transactionItem->products_id = $item['id'];
                    $transactionItem->transactions_id = $transaction->id;
                    $transactionItem->quantity = $item['quantity'];
                    $transactionItem->save();
                }

                $transaction->refresh();
            });

            return ResponseFormatter::success(
                $transaction->load('transaction_items.product'),
                'transaksi berhasil'
            );
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
