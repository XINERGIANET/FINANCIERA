<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\Quota;
use App\Models\Payment;
use App\Models\Expense;
use App\Models\Contract;
use App\Models\User;
use App\Models\Transfer;

class WebController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();

        /* Administrador */

        /* Inicio */

        $home_sales_1 = Payment::active()->where('payment_method_id', 1)->when($request->start_date_4, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date_4, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        })->when($request->seller_id_1, function ($query, $seller_id) {
            return $query->whereHas('quota.contract', function ($query) use ($seller_id) {
                return $query->where('seller_id', $seller_id);
            });
        })->sum('amount');

        // Sum only expensePayments with payment_method_id = 1
        $home_expenses_1 = Expense::active()->whereHas('expensePayments', function ($q) {
            return $q->where('payment_method_id', 1);
        })
            ->when($request->start_date_4, function ($query, $start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })->when($request->end_date_4, function ($query, $end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })->when($request->seller_id_1, function ($query, $seller_id) {
                return $query->where('seller_id', $seller_id);
            })->with(['expensePayments' => function ($q) {
                $q->where('payment_method_id', 1);
            }])->get()
            ->sum(function ($expense) {
                return $expense->expensePayments->sum('amount');
            });

        $home_transfers_1_from = Transfer::active()->when($request->seller_id_1, function ($query, $seller_id) {
            return $query->where('from_seller_id', $seller_id);
        })->when($request->start_date_4, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date_4, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        })->where('type', 'seller')->sum('amount');

        $home_transfers_1_to = Transfer::active()->when($request->seller_id_1, function ($query, $seller_id) {
            return $query->where('to_seller_id', $seller_id);
        })->when($request->start_date_4, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date_4, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        })->where('type', 'seller')->sum('amount');

        $home_sales_1 = $home_sales_1 - $home_expenses_1 - $home_transfers_1_from + $home_transfers_1_to;

        /* Cuadre general */

        // $sales_1 = Payment::active()->where('payment_method_id', 1)->when($request->start_date_3, function($query, $start_date){
        //     return $query->whereDate('date', '>=', $start_date);
        // })->when($request->end_date_3, function($query, $end_date){
        //     return $query->whereDate('date', '<=', $end_date);
        // })->sum('amount');

        // $expenses_1 = Expense::active()->whereHas('expensePayments', function($q){
        //         return $q->where('payment_method_id', 1);
        //     })->when($request->start_date_3, function($query, $start_date){
        //         return $query->whereDate('date', '>=', $start_date);
        //     })->when($request->end_date_3, function($query, $end_date){
        //         return $query->whereDate('date', '<=', $end_date);
        //     })->with('expensePayments')->get()
        //     ->sum(function($expense){
        //         // suma solo payments con payment_method_id = 1
        //         return $expense->expensePayments->where('payment_method_id', 1)->sum('amount');
        //     });

        // $transfers_1_from = Transfer::active()->where('type', 'payment_method')->where('from_payment_method_id', 1)->sum('amount');

        // $transfers_1_to = Transfer::active()->where('type', 'payment_method')->where('to_payment_method_id', 1)->sum('amount');

        // $sales_2 = Payment::active()->where('payment_method_id', 2)->when($request->start_date_3, function($query, $start_date){
        //     return $query->whereDate('date', '>=', $start_date);
        // })->when($request->end_date_3, function($query, $end_date){
        //     return $query->whereDate('date', '<=', $end_date);
        // })->sum('amount');

        // $expenses_2 = Expense::active()->whereHas('expensePayments', function($q){
        //         return $q->where('payment_method_id', 2);
        //     })->when($request->start_date_3, function($query, $start_date){
        //         return $query->whereDate('date', '>=', $start_date);
        //     })->when($request->end_date_3, function($query, $end_date){
        //         return $query->whereDate('date', '<=', $end_date);
        //     })->with('expensePayments')->get()
        //     ->sum(function($expense){
        //         // suma solo payments con payment_method_id = 2
        //         return $expense->expensePayments->where('payment_method_id', 2)->sum('amount');
        //     });

        // $transfers_2_from = Transfer::active()->where('type', 'payment_method')->where('from_payment_method_id', 2)->sum('amount');

        // $transfers_2_to = Transfer::active()->where('type', 'payment_method')->where('to_payment_method_id', 2)->sum('amount');

        // $sales_3 = Payment::active()->where('payment_method_id', 3)->when($request->start_date_3, function($query, $start_date){
        //     return $query->whereDate('date', '>=', $start_date);
        // })->when($request->end_date_3, function($query, $end_date){
        //     return $query->whereDate('date', '<=', $end_date);
        // })->sum('amount');

        // $expenses_3 = Expense::active()->whereHas('expensePayments', function($q){
        //         return $q->where('payment_method_id', 3);
        //     })->when($request->start_date_3, function($query, $start_date){
        //         return $query->whereDate('date', '>=', $start_date);
        //     })->when($request->end_date_3, function($query, $end_date){
        //         return $query->whereDate('date', '<=', $end_date);
        //     })->with('expensePayments')->get()
        //     ->sum(function($expense){
        //         // suma solo payments con payment_method_id = 3
        //         return $expense->expensePayments->where('payment_method_id', 3)->sum('amount');
        //     });

        // $transfers_3_from = Transfer::active()->where('type', 'payment_method')->where('from_payment_method_id', 3)->sum('amount');

        // $transfers_3_to = Transfer::active()->where('type', 'payment_method')->where('to_payment_method_id', 3)->sum('amount');

        // $sales_4 = Payment::active()->where('payment_method_id', 4)->when($request->start_date_3, function($query, $start_date){
        //     return $query->whereDate('date', '>=', $start_date);
        // })->when($request->end_date_3, function($query, $end_date){
        //     return $query->whereDate('date', '<=', $end_date);
        // })->sum('amount');

        // $expenses_4 = Expense::active()->whereHas('expensePayments', function($q){
        //         return $q->where('payment_method_id', 4);
        //     })->when($request->start_date_3, function($query, $start_date){
        //         return $query->whereDate('date', '>=', $start_date);
        //     })->when($request->end_date_3, function($query, $end_date){
        //         return $query->whereDate('date', '<=', $end_date);
        //     })->with('expensePayments')->get()
        //     ->sum(function($expense){
        //         // suma solo payments con payment_method_id = 4
        //         return $expense->expensePayments->where('payment_method_id', 4)->sum('amount');
        //     });

        // $transfers_4_from = Transfer::active()->where('type', 'payment_method')->where('from_payment_method_id', 4)->sum('amount');

        // $transfers_4_to = Transfer::active()->where('type', 'payment_method')->where('to_payment_method_id', 4)->sum('amount');

        // $sales_5 = Payment::active()->where('payment_method_id', 5)->when($request->start_date_3, function($query, $start_date){
        //     return $query->whereDate('date', '>=', $start_date);
        // })->when($request->end_date_3, function($query, $end_date){
        //     return $query->whereDate('date', '<=', $end_date);
        // })->sum('amount');

        // $expenses_5 = Expense::active()->whereHas('expensePayments', function($q){
        //         return $q->where('payment_method_id', 5);
        //     })->when($request->start_date_3, function($query, $start_date){
        //         return $query->whereDate('date', '>=', $start_date);
        //     })->when($request->end_date_3, function($query, $end_date){
        //         return $query->whereDate('date', '<=', $end_date);
        //     })->with('expensePayments')->get()->sum(function($expense){
        //         // suma solo payments con payment_method_id = 4
        //         return $expense->expensePayments->where('payment_method_id', 4)->sum('amount');
        //     });

        // $transfers_5_from = Transfer::active()->where('type', 'payment_method')->where('from_payment_method_id', 5)->sum('amount');

        // $transfers_5_to = Transfer::active()->where('type', 'payment_method')->where('to_payment_method_id', 5)->sum('amount');

        // // $sales_6 = 0; // Pagos Caja chica

        // // $expenses_6 = Expense::active()->whereHas('expensePayments', function($q){
        // //         return $q->where('payment_method_id', 6);
        // //     })->when($request->start_date_3, function($query, $start_date){
        // //         return $query->whereDate('date', '>=', $start_date);
        // //     })->when($request->end_date_3, function($query, $end_date){
        // //         return $query->whereDate('date', '<=', $end_date);
        // //     })->with('expensePayments')->get()->sum('amount');

        // // $transfers_6_to = Transfer::active()->where('type', 'payment_method')->where('to_payment_method_id', 6)->sum('amount');

        // $sales_1 = $sales_1 - $expenses_1 - $transfers_1_from + $transfers_1_to;
        // $sales_2 = $sales_2 - $expenses_2 - $transfers_2_from + $transfers_2_to;
        // $sales_3 = $sales_3 - $expenses_3 - $transfers_3_from + $transfers_3_to;
        // $sales_4 = $sales_4 - $expenses_4 - $transfers_4_from + $transfers_4_to;
        // $sales_5 = $sales_5 - $expenses_5 - $transfers_5_from + $transfers_5_to;
        // // $sales_6 = $sales_6 - $expenses_6 + $transfers_6_to;
        // $total = $sales_1 + $sales_2 + $sales_3 + $sales_4 + $sales_5; // + $sales_6;


        // CARTERA TOTAL : suma de deuda entre las fechas establecidas
        // Cartera total: suma de deuda entre las fechas establecidas.
        // Usamos el scope active() y aplicamos todos los filtros sobre la relación `contract`
        // para evitar filtrar por columnas que no pertenecen a la tabla `quotas`.

        $wallet_total = Quota::active()
            ->when($request->start_date_1, function ($query, $start_date) {
                return $query->whereHas('contract', function ($q) use ($start_date) {
                    return $q->whereDate('date', '>=', $start_date);
                });
            })
            ->when($request->end_date_1, function ($query, $end_date) {
                return $query->whereHas('contract', function ($q) use ($end_date) {
                    return $q->whereDate('date', '<=', $end_date);
                });
            })
            ->when($user->hasRole('seller'), function ($query) use ($user) {
                return $query->whereHas('contract', function ($q) use ($user) {
                    return $q->where('seller_id', $user->id);
                });
            })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->where('paid', 0)
            ->sum('debt');

        // DEUDA TOTAL : CUOTAS QUE FALTAN PAGAR POR CLIENTES MOROSOS
        $due_total = Quota::when($request->start_date_1, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date_1, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->where('paid', 0)
            ->whereHas('contract', function ($q) { // suma de due_days de todos los payments de las cuotas del contrato > 0
                return $q->whereRaw("(select coalesce(sum(p.due_days),0) from payments p inner join quotas qt on p.quota_id = qt.id where qt.contract_id = contracts.id) > 0");
            })->sum('debt');

        $payments = Payment::active()->when($request->start_date_1, function ($query, $start_date) {
            return $query->whereHas('quota.contract', function ($query) use ($start_date) {
                return $query->whereDate('date', '>=', $start_date);
            });
        })->when($request->end_date_1, function ($query, $end_date) {
            return $query->whereHas('quota.contract', function ($query) use ($end_date) {
                return $query->whereDate('date', '<=', $end_date);
            });
        })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('quota.contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('quota.contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->sum('amount');

        // Load expenses and sum using accessor `amount` (which sums expensePayments)
        $expenses = Expense::when($request->start_date_1, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date_1, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->where('seller_id', $seller_id);
            })
            ->active()->with('expensePayments')->get()->sum('amount');

        $today_real = $payments - $expenses;

        // PAGOS DE HOY : todos los pagos de now()
        //Cuotas (mismo criterio que el modal)
        $today_payments_people = Payment::active()
            ->when($request->start_date_1, function ($query, $start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })
            ->when($request->end_date_1, function ($query, $end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('quota.contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('quota.contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->with('quota.contract')
            ->get()
            ->groupBy(function ($payment) {
                $quota = $payment->quota;
                $contractId = $quota && $quota->contract ? $quota->contract->id : 'none';
                $quotaNumber = $quota ? $quota->number : 'none';
                return $contractId . '_' . $quotaNumber;
            })
            ->count();

        $section = $request->query('section');

        $paymentsByMethod = Payment::active()
            ->when($request->start_date_3, function ($q, $d) {
                return $q->whereDate('date', '>=', $d);
            })
            ->when($request->end_date_3, function ($q, $d) {
                return $q->whereDate('date', '<=', $d);
            })
            ->selectRaw('payment_method_id, COALESCE(SUM(amount),0) as total')
            ->groupBy('payment_method_id')
            ->pluck('total', 'payment_method_id');

        // Sum from expense_payments (detailed payments)
        $expensesByMethodPayments = DB::table('expenses_payments as ep')
            ->join('expenses', 'ep.expenses_id', '=', 'expenses.id')
            ->when($request->start_date_3, function ($q, $d) {
                return $q->whereDate('expenses.date', '>=', $d);
            })
            ->when($request->end_date_3, function ($q, $d) {
                return $q->whereDate('expenses.date', '<=', $d);
            })
            ->where('expenses.deleted', 0)
            ->selectRaw('ep.payment_method_id, COALESCE(SUM(ep.amount),0) as total')
            ->groupBy('ep.payment_method_id')
            ->pluck('total', 'payment_method_id');

        // Sum from expenses.amounts (the main amount stored on the expense record)
        $expensesByMethodAmounts = DB::table('expenses')
            ->when($request->start_date_3, function ($q, $d) {
                return $q->whereDate('date', '>=', $d);
            })
            ->when($request->end_date_3, function ($q, $d) {
                return $q->whereDate('date', '<=', $d);
            })
            ->where('deleted', 0)
            ->selectRaw('payment_method_id, COALESCE(SUM(amounts),0) as total')
            ->groupBy('payment_method_id')
            ->pluck('total', 'payment_method_id');

        // Merge both sources: for each payment method, sum amounts + payments
        $expensesByMethodArr = [];
        foreach ($expensesByMethodPayments as $pm => $total) {
            $expensesByMethodArr[$pm] = floatval($total);
        }
        foreach ($expensesByMethodAmounts as $pm => $total) {
            $expensesByMethodArr[$pm] = floatval(($expensesByMethodArr[$pm] ?? 0) + $total);
        }

        $expensesByMethod = collect($expensesByMethodArr);

        $transfersFrom = Transfer::active()->where('type', 'payment_method')
            ->when($request->start_date_3, function ($q, $d) {
                return $q->whereDate('date', '>=', $d);
            })
            ->when($request->end_date_3, function ($q, $d) {
                return $q->whereDate('date', '<=', $d);
            })
            ->selectRaw('from_payment_method_id as pm, COALESCE(SUM(amount),0) as total')
            ->groupBy('from_payment_method_id')
            ->pluck('total', 'pm');

        $transfersTo = Transfer::active()->where('type', 'payment_method')
            ->when($request->start_date_3, function ($q, $d) {
                return $q->whereDate('date', '>=', $d);
            })
            ->when($request->end_date_3, function ($q, $d) {
                return $q->whereDate('date', '<=', $d);
            })
            ->selectRaw('to_payment_method_id as pm, COALESCE(SUM(amount),0) as total')
            ->groupBy('to_payment_method_id')
            ->pluck('total', 'pm');

        // obtener todos los métodos y calcular acumulado dinámicamente
        $payment_methods = PaymentMethod::active()->get()->map(function ($pm) use ($paymentsByMethod, $expensesByMethod, $transfersFrom, $transfersTo) {
            $id = $pm->id;
            $payments = floatval($paymentsByMethod[$id] ?? 0);
            $expenses = floatval($expensesByMethod[$id] ?? 0);
            $from = floatval($transfersFrom[$id] ?? 0);
            $to = floatval($transfersTo[$id] ?? 0);

            // Acumulado original
            $pm->acumulado = $payments - $expenses - $from + $to;

            return $pm;
        })->values();

        return view('index', compact(
            'today_payments',
            'today_projected',
            'today_real',
            'active_clients',
            'due_clients',
            'home_sales_1',
            'payment_methods',
            'due_total',
            'wallet_total',
            'requested_amount',
            'expenses',
            'sales_totals_1',
            'expenses_totals_1',
            'sales_totals_2',
            'expenses_totals_2',
            'sellers',
            'seller_wallet',
            'today_timely_payments',
            'due_quotas',
            'section',
            'admincredits'
        ));
    }

    public function apiReniec(Request $request)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . config('apireniec.token')
        ])->get(config('apireniec.url'), [
            'numero' => $request->dni
        ]);

        $data = $response->json();

        if ($response->successful()) {

            return response()->json([
                'status' => true,
                'name' => $data['nombres'] . ' ' . $data['apellidoPaterno'] . ' ' . $data['apellidoMaterno']
            ]);
        } else {

            return response()->json([
                'status' => false
            ]);
        }
    }

    public function indicadores(Request $request)
    {
        $user = auth()->user();


        $home_sales_1 = Payment::active()->where('payment_method_id', 1)->when($request->start_date_4, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date_4, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        })->when($request->seller_id_1, function ($query, $seller_id) {
            return $query->whereHas('quota.contract', function ($query) use ($seller_id) {
                return $query->where('seller_id', $seller_id);
            });
        })->sum('amount');

        // Sum only expensePayments with payment_method_id = 1
        $home_expenses_1 = Expense::active()->whereHas('expensePayments', function ($q) {
            return $q->where('payment_method_id', 1);
        })->when($request->start_date_4, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date_4, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        })->when($request->seller_id_1, function ($query, $seller_id) {
            return $query->where('seller_id', $seller_id);
        })->with(['expensePayments' => function ($q) {
            $q->where('payment_method_id', 1);
        }])->get()
            ->sum(function ($expense) {
                return $expense->expensePayments->sum('amount');
            });

        $home_transfers_1_from = Transfer::active()->when($request->seller_id_1, function ($query, $seller_id) {
            return $query->where('from_seller_id', $seller_id);
        })->when($request->start_date_4, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date_4, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        })->where('type', 'seller')->sum('amount');

        $home_transfers_1_to = Transfer::active()->when($request->seller_id_1, function ($query, $seller_id) {
            return $query->where('to_seller_id', $seller_id);
        })->when($request->start_date_4, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date_4, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        })->where('type', 'seller')->sum('amount');

        $home_sales_1 = $home_sales_1 - $home_expenses_1 - $home_transfers_1_from + $home_transfers_1_to;

        if ($user->hasRole('credit_manager')) {
            $sellers = User::seller()->active()
                ->where('credit_manager_id', $user->id)
                ->when($request->seller_id_2, function ($q, $seller_id) {
                    return $q->where('id', $seller_id);
                })->get();
        } elseif ($user->hasRole('admin_credit')) {
            $sellers = User::seller()->active()
                ->when($request->credit_manager_id, function ($q, $cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                })->when($request->seller_id_2, function ($q, $seller_id) {
                    return $q->where('id', $seller_id);
                })->get();
        } else {
            $sellers = User::seller()->active()->get();
        }

        // Sum from expenses.amounts (the main amount stored on the expense record)
        $expensesByMethodAmounts = DB::table('expenses')
            ->when($request->start_date_3, function ($q, $d) {
                return $q->whereDate('date', '>=', $d);
            })
            ->when($request->end_date_3, function ($q, $d) {
                return $q->whereDate('date', '<=', $d);
            })
            ->where('deleted', 0)
            ->selectRaw('payment_method_id, COALESCE(SUM(amounts),0) as total')
            ->groupBy('payment_method_id')
            ->pluck('total', 'payment_method_id');

        // Sum from expense_payments (detailed payments)
        $expensesByMethodPayments = DB::table('expenses_payments as ep')
            ->join('expenses', 'ep.expenses_id', '=', 'expenses.id')
            ->when($request->start_date_3, function ($q, $d) {
                return $q->whereDate('expenses.date', '>=', $d);
            })
            ->when($request->end_date_3, function ($q, $d) {
                return $q->whereDate('expenses.date', '<=', $d);
            })
            ->where('expenses.deleted', 0)
            ->selectRaw('ep.payment_method_id, COALESCE(SUM(ep.amount),0) as total')
            ->groupBy('ep.payment_method_id')
            ->pluck('total', 'payment_method_id');

        // Merge both sources: for each payment method, sum amounts + payments
        $expensesByMethodArr = [];
        foreach ($expensesByMethodPayments as $pm => $total) {
            $expensesByMethodArr[$pm] = floatval($total);
        }
        foreach ($expensesByMethodAmounts as $pm => $total) {
            $expensesByMethodArr[$pm] = floatval(($expensesByMethodArr[$pm] ?? 0) + $total);
        }

        $expensesByMethod = collect($expensesByMethodArr);

        $paymentsByMethod = Payment::active()
            ->when($request->start_date_3, function ($q, $d) {
                return $q->whereDate('date', '>=', $d);
            })
            ->when($request->end_date_3, function ($q, $d) {
                return $q->whereDate('date', '<=', $d);
            })
            ->selectRaw('payment_method_id, COALESCE(SUM(amount),0) as total')
            ->groupBy('payment_method_id')
            ->pluck('total', 'payment_method_id');

        $transfersFrom = Transfer::active()->where('type', 'payment_method')
            ->when($request->start_date_3, function ($q, $d) {
                return $q->whereDate('date', '>=', $d);
            })
            ->when($request->end_date_3, function ($q, $d) {
                return $q->whereDate('date', '<=', $d);
            })
            ->selectRaw('from_payment_method_id as pm, COALESCE(SUM(amount),0) as total')
            ->groupBy('from_payment_method_id')
            ->pluck('total', 'pm');


        $transfersTo = Transfer::active()->where('type', 'payment_method')
            ->when($request->start_date_3, function ($q, $d) {
                return $q->whereDate('date', '>=', $d);
            })
            ->when($request->end_date_3, function ($q, $d) {
                return $q->whereDate('date', '<=', $d);
            })
            ->selectRaw('to_payment_method_id as pm, COALESCE(SUM(amount),0) as total')
            ->groupBy('to_payment_method_id')
            ->pluck('total', 'pm');

        // obtener todos los métodos y calcular acumulado dinámicamente
        $payment_methods = PaymentMethod::active()->get()->map(function ($pm) use ($paymentsByMethod, $expensesByMethod, $transfersFrom, $transfersTo) {
            $id = $pm->id;
            $payments = floatval($paymentsByMethod[$id] ?? 0);
            $expenses = floatval($expensesByMethod[$id] ?? 0);
            $from = floatval($transfersFrom[$id] ?? 0);
            $to = floatval($transfersTo[$id] ?? 0);

            // Acumulado original
            $pm->acumulado = $payments - $expenses - $from + $to;

            return $pm;
        })->values();
        return view('dashboard.indicadores', compact('sellers', 'payment_methods', 'home_sales_1'));
    }

    public function rentabilidad(Request $request)
    {
        $user = auth()->user();
        $admincredits = User::where('role', 'credit_manager')->active()->get();
        // Obtener TODOS los sellers para el select, el filtrado se hace en el frontend
        $sellers = User::seller()->active()->get();

        $wallet_total = Quota::active()
            ->when($request->start_date_1, function ($query, $start_date) {
                return $query->whereHas('contract', function ($q) use ($start_date) {
                    return $q->whereDate('date', '>=', $start_date);
                });
            })
            ->when($request->end_date_1, function ($query, $end_date) {
                return $query->whereHas('contract', function ($q) use ($end_date) {
                    return $q->whereDate('date', '<=', $end_date);
                });
            })
            ->when($user->hasRole('seller'), function ($query) use ($user) {
                return $query->whereHas('contract', function ($q) use ($user) {
                    return $q->where('seller_id', $user->id);
                });
            })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })->when($request->credit_manager_id, function ($query, $cm_id) {})

            ->where('paid', 0)
            ->sum('debt');

        $due_total = Quota::when($request->start_date_1, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date_1, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->where('paid', 0)
            ->whereHas('contract', function ($q) { // suma de due_days de todos los payments de las cuotas del contrato > 0
                return $q->whereRaw("(select coalesce(sum(p.due_days),0) from payments p inner join quotas qt on p.quota_id = qt.id where qt.contract_id = contracts.id) > 0");
            })->sum('debt');

        // Precargar cache de cuotas grupales para evitar N+1 queries
        // Obtener todos los contratos grupales que podrían estar involucrados
        $groupContractsQuery = Contract::where('client_type', 'Grupo')
            ->where('deleted', 0)
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->where('seller_id', $seller_id);
            });
        
        $groupContractIds = $groupContractsQuery->pluck('id')->toArray();
        
        // Precargar todas las cuotas de contratos grupales y crear cache
        $groupQuotasCache = collect();
        if (!empty($groupContractIds)) {
            $groupQuotasCache = Quota::whereIn('contract_id', $groupContractIds)
                ->get()
                ->groupBy(function($quota) {
                    return $quota->contract_id . '_' . $quota->number;
                })
                ->map(function($quotas) {
                    // Verificar si todas las cuotas del mismo número están pagadas
                    return $quotas->every(function($q) {
                        return $q->paid == 1;
                    });
                });
        }

        // PAGOS : todos los pagos filtrados por fecha
        //Personas por documento
        $today_payments_people = Payment::active()
            ->when($request->start_date_1, function ($query, $start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })
            ->when($request->end_date_1, function ($query, $end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('quota.contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('quota.contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->with('quota.contract')
            ->get()
            ->groupBy(function ($payment) {
                $quota = $payment->quota;
                $contractId = $quota && $quota->contract ? $quota->contract->id : 'none';
                $quotaNumber = $quota ? $quota->number : 'none';
                return $contractId . '_' . $quotaNumber;
            })
            ->count();
        //Monto en soles
        $today_payments = Payment::active()
            ->when($request->start_date_1, function ($query, $start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })
            ->when($request->end_date_1, function ($query, $end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('quota.contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('quota.contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->sum('amount');

        //Pagos adelantados de hoy
        //Personas por documento
        $today_advance_payments_people = Payment::active()
            ->when($request->start_date_1, function ($query, $start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })
            ->when($request->end_date_1, function ($query, $end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('quota.contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('quota.contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->whereRaw('DATE(payments.date) < (SELECT DATE(quotas.date) FROM quotas WHERE quotas.id = payments.quota_id)')
            ->with('quota.contract')
            ->get()
            ->groupBy(function ($payment) {
                $quota = $payment->quota;
                $contractId = $quota && $quota->contract ? $quota->contract->id : 'none';
                $quotaNumber = $quota ? $quota->number : 'none';
                return $contractId . '_' . $quotaNumber;
            })
            ->count();
        //Monto en soles
        $today_advance_payments = Payment::active()
            ->when($request->start_date_1, function ($query, $start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })
            ->when($request->end_date_1, function ($query, $end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('quota.contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('quota.contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->whereRaw('DATE(payments.date) < (SELECT DATE(quotas.date) FROM quotas WHERE quotas.id = payments.quota_id)')
            ->sum('amount');

        //PAGOS PUNTUALES DE HOY : pagos de cuotas con la misma fecha (puntuales)

        //Personas por documento
        $today_timely_payments_people = Payment::active()
            ->when($request->start_date_1, function ($query, $start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })
            ->when($request->end_date_1, function ($query, $end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('quota.contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('quota.contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->whereRaw('DATE(payments.date) = (SELECT DATE(quotas.date) FROM quotas WHERE quotas.id = payments.quota_id)')
            ->with('quota.contract')
            ->get()
            ->flatMap(function ($payment) use ($groupQuotasCache) {
                $contract = $payment->quota->contract ?? null;
                $quota = $payment->quota ?? null;
                if (!$contract || !$quota) return [];

                // Si es contrato Personal
                if ($contract->client_type == 'Personal') {
                    // Si tiene people en el pago, usar esos datos
                    if ($payment->people) {
                        $people = json_decode($payment->people);
                        if ($people) {
                            // Si es un objeto único
                            if (is_object($people) && isset($people->document)) {
                                return [$people->document];
                            }
                            // Si es un array
                            if (is_array($people)) {
                                return collect($people)->pluck('document')->filter();
                            }
                        }
                    }
                    // Si no tiene people, usar el documento del contrato
                    return $contract->document ? [$contract->document] : [];
                }
                // Si es contrato Grupo
                elseif ($contract->client_type == 'Grupo') {
                    // Usar el cache para verificar si todas las cuotas del mismo número están pagadas
                    $cacheKey = $contract->id . '_' . $quota->number;
                    $allPaid = $groupQuotasCache->get($cacheKey, false);
                    
                    // Si no todas están pagadas, no contar
                    if (!$allPaid) {
                        return [];
                    }
                    
                    // Usar el nombre del grupo en lugar de documentos individuales
                    return $contract->group_name ? [$contract->group_name] : [];
                }
                
                return [];
            })
            ->unique()
            ->count();
        //Monto en soles
        $today_timely_payments = Payment::active()
            ->when($request->start_date_1, function ($query, $start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })
            ->when($request->end_date_1, function ($query, $end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('quota.contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('quota.contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->whereRaw('DATE(payments.date) = (SELECT DATE(quotas.date) FROM quotas WHERE quotas.id = payments.quota_id)')
            ->sum('amount');

        //PROYECTADO PARA HOY : todo lo que está en el rango de fechas (pagado y no pagado)

        //Personas por documento
        $today_projected_people = Quota::active()
            ->when($request->start_date_1, function ($query, $start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })
            ->when($request->end_date_1, function ($query, $end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->count();
        //Monto en soles
        $today_projected = Quota::active()
            ->when($request->start_date_1, function ($query, $start_date) {
                return $query->whereDate('date', '>=', $start_date);
            })
            ->when($request->end_date_1, function ($query, $end_date) {
                return $query->whereDate('date', '<=', $end_date);
            })
            ->when($request->credit_manager_id, function ($query, $cm_id) {
                return $query->whereHas('contract.seller', function ($q) use ($cm_id) {
                    return $q->where('credit_manager_id', $cm_id);
                });
            })
            ->when($request->seller_id_2, function ($query, $seller_id) {
                return $query->whereHas('contract', function ($q) use ($seller_id) {
                    return $q->where('seller_id', $seller_id);
                });
            })
            ->sum('amount');

        return view('dashboard.rentabilidad', compact('admincredits', 'wallet_total', 'sellers', 'due_total', 'today_payments_people', 'today_payments', 'today_timely_payments_people', 'today_timely_payments', 'today_projected_people', 'today_projected', 'today_advance_payments', 'today_advance_payments_people'));
    }


    public function productividad(Request $request)
    {

        $user = auth()->user();
        $admincredits = User::where('role', 'credit_manager')->active()->get();
        $sellers = User::seller()->active()->get();
        // TOTAL DE CLIENTES (únicos por document|group_name) respetando mismos filtros
        $total_clients_count = DB::table('contracts')
            ->leftJoin('users', 'contracts.seller_id', '=', 'users.id')
            ->when($user->hasRole('seller'), function ($q) {
                return $q->where('contracts.seller_id', auth()->user()->id);
            })
            ->when($request->credit_manager_id, function ($q, $cm_id) {
                return $q->where('users.credit_manager_id', $cm_id);
            })
            ->when($request->seller_id_2, function ($q, $seller_id) {
                return $q->where('contracts.seller_id', $seller_id);
            })
            ->when($request->start_date_2, function ($q, $start_date) {
                return $q->whereDate('contracts.date', '>=', $start_date);
            })
            ->when($request->end_date_2, function ($q, $end_date) {
                return $q->whereDate('contracts.date', '<=', $end_date);
            })
            ->where('contracts.deleted', 0)
            ->where('contracts.paid', 0)
            ->selectRaw("COUNT(DISTINCT CONCAT(COALESCE(contracts.document,''),'|',COALESCE(contracts.group_name,''))) as total")
            ->value('total');

        $cutoff = now()->subDays(120)->toDateString();


        $due_clients = DB::table('contracts')
            ->join('quotas', 'quotas.contract_id', 'contracts.id')
            ->leftJoin('payments', 'payments.quota_id', 'quotas.id')
            ->leftJoin('users', 'contracts.seller_id', '=', 'users.id')
            ->when($user->hasRole('seller'), function ($q) {
                return $q->where('contracts.seller_id', auth()->user()->id);
            })
            ->when($request->credit_manager_id, function ($q, $cm_id) {
                return $q->where('users.credit_manager_id', $cm_id);
            })
            ->when($request->seller_id_2, function ($q, $seller_id) {
                return $q->where('contracts.seller_id', $seller_id);
            })
            ->when($request->start_date_2, function ($q, $start_date) {
                return $q->whereDate('contracts.date', '>=', $start_date);
            })
            ->when($request->end_date_2, function ($q, $end_date) {
                return $q->whereDate('contracts.date', '<=', $end_date);
            })
            ->where(function ($q) use ($cutoff) {
                $q->where('payments.due_days', '>=', 120)
                    ->orWhere(function ($q2) use ($cutoff) {
                        $q2->where('quotas.paid', 0)
                            ->whereDate('quotas.date', '<=', $cutoff);
                    });
            })
            ->where('contracts.deleted', 0)
            ->selectRaw("COUNT(DISTINCT CONCAT(COALESCE(contracts.document,''),'|',COALESCE(contracts.group_name,''))) as total")
            ->value('total');

        $active_clients = max(0, intval($total_clients_count) - intval($due_clients));


        $seller_wallet = Quota::when($user->hasRole('seller'), function ($query) {
            return $query->whereHas('contract', function ($query) {
                return $query->where('seller_id', auth()->user()->id);
            });
        })->when($request->credit_manager_id, function ($query, $cm_id) {
            return $query->whereHas('contract.seller', function ($q) use ($cm_id) {
                return $q->where('credit_manager_id', $cm_id);
            });
        })->when($request->seller_id_2, function ($query, $seller_id) {
            return $query->whereHas('contract', function ($query) use ($seller_id) {
                return $query->where('seller_id', $seller_id);
            });
        })->when($request->start_date_2, function ($query, $start_date) {
            return $query->whereHas('contract', function ($query) use ($start_date) {
                return $query->whereDate('date', '>=', $start_date);
            });
        })->when($request->end_date_2, function ($query, $end_date) {
            return $query->whereHas('contract', function ($query) use ($end_date) {
                return $query->whereDate('date', '<=', $end_date);
            });
        })->whereHas('contract', function ($query) {
            return $query->where('deleted', 0);
        })->where('paid', 0)->sum('debt');


        $requested_amount = Contract::active()->when($user->hasRole('seller'), function ($query) {
            return $query->where('seller_id', auth()->user()->id);
        })->when($request->credit_manager_id, function ($query, $cm_id) {
            return $query->whereHas('seller', function ($q) use ($cm_id) {
                return $q->where('credit_manager_id', $cm_id);
            });
        })->when($request->seller_id_2, function ($query, $seller_id) {
            return $query->where('seller_id', $seller_id);
        })->when($request->start_date_2, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date_2, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        })->sum('requested_amount');



        $due_quotas = Quota::when($user->hasRole('seller'), function ($query) {
            return $query->whereHas('contract', function ($query) {
                return $query->where('seller_id', auth()->user()->id);
            });
        })->when($request->credit_manager_id, function ($query, $cm_id) {
            return $query->whereHas('contract.seller', function ($q) use ($cm_id) {
                return $q->where('credit_manager_id', $cm_id);
            });
        })->when($request->seller_id_2, function ($query, $seller_id) {
            return $query->whereHas('contract', function ($query) use ($seller_id) {
                return $query->where('seller_id', $seller_id);
            });
        })->when($request->start_date_2, function ($query, $start_date) {
            return $query->whereHas('contract', function ($query) use ($start_date) {
                return $query->whereDate('date', '>=', $start_date);
            });
        })->when($request->end_date_2, function ($query, $end_date) {
            return $query->whereHas('contract', function ($query) use ($end_date) {
                return $query->whereDate('date', '<=', $end_date);
            });
        })->whereHas('contract', function ($query) {
            return $query->where('deleted', 0);
        })->where('paid', 0)
            ->count();

        return view('dashboard.productividad', compact('admincredits', 'sellers', 'active_clients', 'due_clients', 'total_clients_count', 'seller_wallet', 'requested_amount', 'due_quotas'));
    }

    public function rentabilidadCardDetails(Request $request)
    {
        $card = $request->card;
        $allowedCards = ['advance', 'today', 'timely', 'projected'];

        if (!in_array($card, $allowedCards, true)) {
            return response()->json([
                'status' => false,
                'error' => 'Tipo de tarjeta inválido'
            ], 422);
        }

        $startDate = $request->start_date_1;
        $endDate = $request->end_date_1;
        $creditManagerId = $request->credit_manager_id;
        $sellerId = $request->seller_id_2;

        if ($card === 'projected') {
            $quotas = Quota::active()
                ->when($startDate, function ($query) use ($startDate) {
                    return $query->whereDate('date', '>=', $startDate);
                })
                ->when($endDate, function ($query) use ($endDate) {
                    return $query->whereDate('date', '<=', $endDate);
                })
                ->when($creditManagerId, function ($query) use ($creditManagerId) {
                    return $query->whereHas('contract.seller', function ($q) use ($creditManagerId) {
                        return $q->where('credit_manager_id', $creditManagerId);
                    });
                })
                ->when($sellerId, function ($query) use ($sellerId) {
                    return $query->whereHas('contract', function ($q) use ($sellerId) {
                        return $q->where('seller_id', $sellerId);
                    });
                })
                ->with('contract')
                ->orderBy('date', 'DESC')
                ->orderBy('id', 'DESC')
                ->limit(300)
                ->get();

            $items = $quotas->map(function ($quota) {
                $contract = $quota->contract;
                return [
                    'client' => $contract ? $contract->client() : 'N/A',
                    'contract_date' => $contract && $contract->date ? $contract->date->format('d/m/Y') : null,
                    'quota_number' => $quota->number,
                    'person_name' => $quota->person_name,
                    'amount' => $quota->amount,
                    'debt' => $quota->debt,
                    'due_date' => $quota->date ? $quota->date->format('d/m/Y') : null,
                    'paid' => $quota->paid ? true : false,
                ];
            })->values();

            return response()->json([
                'status' => true,
                'type' => 'quotas',
                'total' => $items->count(),
                'items' => $items,
            ]);
        }

        $payments = Payment::active()
            ->when($startDate, function ($query) use ($startDate) {
                return $query->whereDate('date', '>=', $startDate);
            })
            ->when($endDate, function ($query) use ($endDate) {
                return $query->whereDate('date', '<=', $endDate);
            })
            ->when($creditManagerId, function ($query) use ($creditManagerId) {
                return $query->whereHas('quota.contract.seller', function ($q) use ($creditManagerId) {
                    return $q->where('credit_manager_id', $creditManagerId);
                });
            })
            ->when($sellerId, function ($query) use ($sellerId) {
                return $query->whereHas('quota.contract', function ($q) use ($sellerId) {
                    return $q->where('seller_id', $sellerId);
                });
            })
            ->when($card === 'advance', function ($query) {
                return $query->whereRaw('DATE(payments.date) < (SELECT DATE(quotas.date) FROM quotas WHERE quotas.id = payments.quota_id)');
            })
            ->when($card === 'timely', function ($query) {
                return $query->whereRaw('DATE(payments.date) = (SELECT DATE(quotas.date) FROM quotas WHERE quotas.id = payments.quota_id)');
            })
            ->with(['quota.contract', 'payment_method'])
            ->orderBy('date', 'DESC')
            ->orderBy('id', 'DESC')
            ->limit(300)
            ->get();

        $items = $payments
            ->groupBy(function ($payment) {
                $quota = $payment->quota;
                $contractId = $quota && $quota->contract ? $quota->contract->id : 'none';
                $quotaNumber = $quota ? $quota->number : 'none';
                return $contractId . '_' . $quotaNumber;
            })
            ->map(function ($group) {
                $first = $group->first();
                $quota = $first->quota;
                $contract = $quota ? $quota->contract : null;

                $methods = $group->map(function ($p) {
                    $name = optional($p->payment_method)->name ?? 'N/A';
                    return $name === 'Efectivo' ? 'Retanqueo' : $name;
                })->unique()->values()->toArray();

                $paymentDate = $group->max('date');
                $dueDays = $group->sortByDesc('date')->first()->due_days ?? null;

                return [
                    'client' => $contract ? $contract->client() : 'N/A',
                    'contract_date' => $contract && $contract->date ? $contract->date->format('d/m/Y') : null,
                    'quota_number' => $quota ? $quota->number : null,
                    'person_name' => $quota ? $quota->person_name : null,
                    'amount' => $group->sum('amount'),
                    'payment_method' => implode(' / ', $methods),
                    'payment_date' => $paymentDate ? $paymentDate->format('d/m/Y') : null,
                    'due_days' => $dueDays,
                ];
            })
            ->values();

        return response()->json([
            'status' => true,
            'type' => 'payments',
            'total' => $items->count(),
            'items' => $items,
        ]);
    }
}





