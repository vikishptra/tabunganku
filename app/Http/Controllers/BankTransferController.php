<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Xendit\Xendit;
use Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\AccountBankUser;
use Illuminate\Support\Facades\Http;
use App\Http\Requests\CreateGetVABankUser;
use Illuminate\Http\JsonResponse;
use App\Models\DetailTransaksiBank;
use App\Models\DetailSaldoUser;
use App\Models\RuleTransaksiBank;
use App\Http\Requests\SimulasiTabunganRencanaRequest;

class BankTransferController extends Controller
{
    public function createVaBankUser(CreateGetVABankUser $request){
        try {
            $secret_key = 'Basic ' . config('xendit.key_auth');
            $external_id = Str::random(10);
            $user = auth()->user();

             // Cek apakah Virtual Account Bank sudah dibuat sebelumnya
            $va_account = AccountBankUser::where('id_user', $user->id)
                ->where('bank_code', $request->bank_code)
                ->first();

            if ($va_account && $va_account->bank_code === $request->bank_code) {
                $errors = "VA " . $request->bank_code . " anda sudah terbuat";
                return $this->messagesError($errors, 400);
            }
            //Jika Belom buat maka Buat Virtual Account Bank baru pada Xendit
            $data_request = Http::withHeaders([
                'Authorization' => $secret_key
            ])->post('https://api.xendit.co/callback_virtual_accounts', [
                'external_id' => 'va-' . $external_id,
                'name' => $user->name,
                'bank_code' => $request->bank_code
            ]);

            $response = $data_request->json();
            if (isset($response)) {
                // Jika Virtual Account Bank berhasil dibuat pada Xendit, simpan data ke database
                $responseID = $response['id'];
                $data_request = Http::withHeaders([
                    'Authorization' => $secret_key
                ])->get('https://api.xendit.co/callback_virtual_accounts/'.$responseID);
                if($data_request['status'] == "ACTIVE") {
                    $transaksi = AccountBankUser::create([
                        'id' => 'va-' . $external_id,
                        'id_user' => $user->id,
                        'bank_code' => $response['bank_code'],
                        'status' => $response['status'],
                        'va_account' => $response['account_number'],
                    ]);

                    return $this->messagesSuccess($transaksi, "ok success", 201);


                }else if ($data_request['status'] == "PENDING") {
                    return $this->messagesError('Virtual Account Sedang Pending Mohon Di Coba Lagi!', 400);
                }
            }else {
                return $this->messagesError('Terjadi Kesalahan Mohon Coba Lagi', 400);
            }
        } catch (\Exception $e) {
            return $this->messagesError('Terjadi Kesalahan ' . $e->getMessage(), 400);
        }
    }



    public function getVABankUser(CreateGetVABankUser $request){
    try {
        $user = auth()->user();
        // Cek apakah Virtual Account Bank sudah dibuat sebelumnya
        $va_account = AccountBankUser::where('id_user', $user->id)
            ->where('bank_code', $request->bank_code)
            ->first();
        if ($va_account && $va_account->bank_code === $request->bank_code) {
            // Jika Virtual Account Bank sudah dibuat sebelumnya, tampilkan nomor Virtual Account Bank
            $response = [
                'va_account' => $va_account->va_account,
                'bank_code' => $va_account->bank_code
            ];

            return $this->messagesSuccess($response, "ok success", 200);
        } else {
            // Jika Virtual Account Bank belum dibuat sebelumnya, buat Virtual Account Bank baru pada Xendit dan simpan data ke database
            $response = $this->createVaBankUser($request);
            $data = $response->getData();
            if ($data->messages != "ok success") {
               return $this->messagesError('Terjadi Kesalahan '.$data->messages, 400);
            }
            $response = [
                'va_account' => $data->data->va_account,
                'bank_code' => $data->data->bank_code
            ];
            return $this->messagesSuccess($response, "ok success", 200);
            
        }
    }catch (\Exception $e) {
        return $this->messagesError('Terjadi Kesalsahan ' . $e->getMessage(), 400);
        }
    }


    public function callbackBank(Request $request){
        $secret_key = 'Basic ' . config('xendit.key_auth');
        $response = $request->all();
        $user = auth()->user();
        $payment_id= "payment_id=".$response['payment_id'];
        $detailTransaksiUser = DetailTransaksiBank::where('id', $response['payment_id'])->first();
        if ($detailTransaksiUser != NULL) {
            return $this->messagesError('Forbidden', 403);
        }        

        $data_request = Http::withHeaders([
            'Authorization' => $secret_key
        ])->get('https://api.xendit.co/callback_virtual_account_payments/'.$payment_id);
        $responseXendit = $data_request->json();
        if ($responseXendit['amount'] == NULL || $responseXendit['amount'] < 0) {
            $detailTransaksiBank = DetailTransaksiBank::create([
                'id' => $responseXendit['payment_id'],
                'id_user' => $user->id,
                'id_va_account' => $responseXendit['external_id'],
                'amount' => $response['amount'],
                'status_trx' => "INVALID",
            ]);
            return $this->messagesError('Gagal topup saldo anda, Mohon di coba lagi ! ', 400);
        }

        $detailTransaksiBank = DetailTransaksiBank::create([
            'id' => $responseXendit['payment_id'],
            'id_user' => $user->id,
            'id_va_account' => $responseXendit['external_id'],
            'amount' => $responseXendit['amount'],
            'status_trx' => "SUCCESS",
        ]);
        $ruleTransaksi = RuleTransaksiBank::where('bank_code', $request->bank_code)->first();
        $detailSaldoUser = DetailSaldoUser::where('id_user', $user->id)->first();
        $detailSaldoUser->saldo += $responseXendit['amount'];
        $detailSaldoUser->saldo -= $ruleTransaksi->rule_transaksi;
        $detailSaldoUser->save();
        

        return $this->messagesSuccess($responseXendit, "ok success", 200);

    }

    public function calculateSimulasiTabungan(SimulasiTabunganRencanaRequest $request)
    {
    try {

         // Ambil input dari user
        $targetTabungan = $request->input('target_tabungan');
        $jumlahUangSaatIni = $request->input('jumlah_uang_saat_ini');
        $kontribusi = $request->input('nabung');

         // Hitung total kontribusi
         $totalKontribusi = $jumlahUangSaatIni;
         foreach ($kontribusi as $frekuensi => $jumlah) {
             if ($frekuensi == 'harian') {
                 $totalKontribusi += $jumlah * 30;
             } elseif ($frekuensi == 'mingguan') {
                 $totalKontribusi += $jumlah * 4;
             }
         }
         
         // Hitung jumlah bulan dan hari yang dibutuhkan untuk mencapai target
         $selisihUang = $targetTabungan - $jumlahUangSaatIni;
         $jumlahHari = round($selisihUang / ($totalKontribusi / 30));
         $jumlahBulan = floor($jumlahHari / 30);
         $jumlahHari %= 30;

        // Hitung tanggal mencapai target
        $tanggal_mencapai_target = date('Y-m-d', strtotime('+' . $jumlahBulan . ' months +' . $jumlahHari . ' days'));

        $tanggal_mencapai_target_indonesia = strftime('%d %B %Y', strtotime($tanggal_mencapai_target));
        // Hitung tahun dan bulan
        $tahun = date('Y', strtotime($tanggal_mencapai_target));
        $bulan = date('F', strtotime($tanggal_mencapai_target));
        $tanggal = date('d', strtotime($tanggal_mencapai_target));
        $hari = date('l', strtotime($tanggal_mencapai_target));
        $tanggal_mencapai_target_indonesia_hari = str_replace(
            ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
            ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'],
            $hari
        );
        $tanggal_mencapai_target_indonesia_bulan = str_replace(
            ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            $bulan
        );

        // Buat response
        $response = [
            'target_tabungan' => $targetTabungan,
            'jumlah_bulan' => $jumlahBulan,
            'tahun' => $tahun,
            'bulan' => $tanggal_mencapai_target_indonesia_bulan,
            'tanggal' => $tanggal,
            'hari' => $tanggal_mencapai_target_indonesia_hari,
        ];

        // Return response dalam format JSON
        return response()->json($response);
    } catch (\Exception $e) {
        return $this->messagesError('Terjadi Kesalsahan ' . $e->getMessage(), 400);
     }
       
    }
    
    

    
    


}




