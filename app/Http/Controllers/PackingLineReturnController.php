<?php

namespace App\Http\Controllers;

use App\Models\SignalBit\MasterPlan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use DB;

class PackingLineReturnController extends Controller
{
    public function waiting()
    {
        return view('return-waiting');
    }

    public function defect()
    {
        return view('return-defect');
    }

    public function rework()
    {
        return view('return-rework');
    }

    public function reject()
    {
        return view('return-reject');
    }

    public function getScannedItemReturnDefect(Request $request)
    {
        $checkReturn = DB::table('output_defect_packing_po_return')
            ->where('kode_numbering', $request->id)
            ->first();

        if ($checkReturn) {
            return response()->json([
                'message' => 'QR sudah pernah dilakukan return defect'
            ], 404);
        }
        
        $data = DB::select("
            SELECT
                *
            FROM
                output_rfts_packing_po_return
            WHERE
                output_rfts_packing_po_return.kode_numbering = ?
        ", [$request->id]);

        if (empty($data)) {
            return response()->json([
                'message' => 'Data QR tidak ditemukan'
            ], 404);
        }

        return response()->json($data[0]);
    }

    public function getScannedItemReturnRework(Request $request)
    {
        $checkReturn = DB::table('output_defect_packing_po_return')
            ->where('kode_numbering', $request->id)
            ->where('defect_status', 'reworked')
            ->first();

        if ($checkReturn) {
            return response()->json([
                'message' => 'QR sudah pernah dilakukan return rework'
            ], 404);
        }
        
        $data = DB::select("
            SELECT
                output_defect_packing_po_return.*,
                output_rfts_packing_po_return.po,
                output_rfts_packing_po_return.kpno,
                output_rfts_packing_po_return.style,
                output_rfts_packing_po_return.color,
                output_rfts_packing_po_return.size,
                output_rfts_packing_po_return.packing_line
            FROM
                output_defect_packing_po_return
            LEFT JOIN output_rfts_packing_po_return ON output_rfts_packing_po_return.id = output_defect_packing_po_return.output_rfts_packing_po_return_id
            WHERE
                output_defect_packing_po_return.kode_numbering = ?
        ", [$request->id]);

        if (empty($data)) {
            return response()->json([
                'message' => 'Data QR tidak ditemukan'
            ], 404);
        }

        return response()->json($data[0]);
    }

    public function getScannedItemReturnReject(Request $request)
    {
        $checkReturn = DB::table('output_reject_packing_po_return')
            ->where('kode_numbering', $request->id)
            ->first();

        if ($checkReturn) {
            return response()->json([
                'message' => 'QR sudah pernah dilakukan return reject'
            ], 404);
        }
        
        $data = DB::select("
            SELECT
                output_defect_packing_po_return.id,
                output_defect_packing_po_return.so_det_id,
                output_defect_packing_po_return.kode_numbering,
                output_defect_packing_po_return.master_plan_id,
                output_defect_packing_po_return.output_rfts_packing_po_return_id,
                output_rfts_packing_po_return.po,
                output_rfts_packing_po_return.kpno,
                output_rfts_packing_po_return.style,
                output_rfts_packing_po_return.color,
                output_rfts_packing_po_return.size,
                output_rfts_packing_po_return.packing_line
            FROM
                output_defect_packing_po_return
            LEFT JOIN output_rfts_packing_po_return ON output_rfts_packing_po_return.id = output_defect_packing_po_return.output_rfts_packing_po_return_id
            WHERE
                output_defect_packing_po_return.kode_numbering = ?

            UNION ALL

            SELECT
                NULL AS id,
                so_det_id,
                kode_numbering,
                master_plan_id,
                id AS output_rfts_packing_po_return_id,
                po,
                kpno,
                style,
                color,
                size,
                packing_line
            FROM
                output_rfts_packing_po_return
            WHERE
                kode_numbering = ?
        ", [$request->id, $request->id]);

        if (empty($data)) {
            return response()->json([
                'message' => 'Data QR tidak ditemukan'
            ], 404);
        }

        return response()->json($data[0]);
    }
}