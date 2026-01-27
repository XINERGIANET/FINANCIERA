<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Contract;
use App\Models\Quota;

class QuotaController extends Controller
{
    public function api(Request $request){
        $contract = Contract::findOrFail($request->contract_id);
        $quotas = Quota::where('contract_id', $request->contract_id)
            ->where('paid', 0)
            ->orderBy('number', 'asc')
            ->get();
        
        if ($contract->client_type == 'Grupo') {
            // Agrupar cuotas por número para grupos
            $groupedQuotas = $quotas->groupBy('number')->map(function($quotaGroup) {
                $firstQuota = $quotaGroup->first();
                $totalAmount = $quotaGroup->sum('amount');
                $totalDebt = $quotaGroup->sum('debt');
                
                $people = $quotaGroup->map(function($quota) {
                    return [
                        'quota_id' => $quota->id,
                        'document' => $quota->person_document,
                        'name' => $quota->person_name,
                        'amount' => $quota->amount,
                        'debt' => $quota->debt,
                    ];
                })->values();
                
                return [
                    'number' => $firstQuota->number,
                    'date' => $firstQuota->date->format('d/m/Y'),
                    'amount' => $totalAmount,
                    'debt' => $totalDebt,
                    'people' => $people
                ];
            })->values();
            
            return response()->json([
                'contract' => $contract,
                'quotas' => $groupedQuotas
            ]);
        } else {
            // Para individuales, mantener el flujo original
            $quotas = $quotas->map(function($quota){
                return [
                    'id' => $quota->id,
                    'number' => $quota->number,
                    'date' => $quota->date->format('d/m/Y'),
                    'amount' => $quota->amount,
                    'debt' => $quota->debt,
                ];
            });

            return response()->json([
                'contract' => $contract,
                'quotas' => $quotas
            ]);
        }
    }
}
