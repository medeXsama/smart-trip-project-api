<?php

namespace App\Http\Controllers;

use Google\Cloud\BigQuery\BigQueryClient;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Exception;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class BigQueryController extends Controller
{
// Method untuk mendapatkan jenis wisata
public function getTypes(Request $request)
{
    $types = [
        'museums',
        'art markets',
        'cafes',
        'traditional villages',
        'cultural villages',
        'villages',
        'shopping malls',
        'meditation retreats',
        'nightlife',
        'regional food',
        'restaurants',
        'royal palaces',
        'spas',
        'top tourist attractions',
        'yoga retreats',
        'hiking trails',
        'lakes',
        'diving spots',
        'caves',
        'mountains',
        'national parks',
        'markets',
        'zoos',
        'theme parks',
        'monuments',
        'snorkeling',
        'surfing spots',
        'temples',
        'water parks',
    ];

    return response()->json($types);
}

public function getKeywords()
{
    $keywords = [
        'dingin',
        'ramai',
        'sepi',
        'bersejarah',
        'modern',
        'alami',
        'artistik',
        'kekeluargaan',
        'mewah',
        'relaksasi',
        'tradisional',
    ];

    return response()->json($keywords);
}


// Method untuk menyimpan data registrasi ke BigQuery
public function insertDataToBigQuery(Request $request)
{
    try {
        $projectId = 'smart-trip-project';
        $datasetId = 'users_dataset';
        $tableId = 'users';

        // Validasi input
        $validated = $request->validate([
            'username' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|string|min:6',
            'preference' => 'required|string|in:indoor,outdoor',
            'keywords' => 'required|array',
            'keywords.*' => 'string|in:dingin,ramai,sepi,bersejarah,modern,alami,artistik,kekeluargaan,mewah,relaksasi,tradisional',
            'types' => 'required|array',
            'types.*' => 'string',
        ]);

        $bigQuery = new BigQueryClient(['projectId' => $projectId]);

        // Cek jika email sudah terdaftar
        $emailCheckQuery = "
            SELECT email 
            FROM `$projectId.$datasetId.$tableId`
            WHERE email = @email
            LIMIT 1
        ";
        $queryJobConfig = $bigQuery->query($emailCheckQuery)
            ->parameters(['email' => $validated['email']]);
        $queryJob = $bigQuery->runQuery($queryJobConfig);

        $emailExists = false;
        foreach ($queryJob as $row) {
            if ($row['email'] === $validated['email']) {
                $emailExists = true;
                break;
            }
        }

        if ($emailExists) {
            return response()->json([
                'error' => 'Email sudah terdaftar. Silakan gunakan email lain atau login.',
            ], 400);
        }

        // Simpan data ke BigQuery
        $id = (string) Uuid::uuid4();
        $data = [
            'id' => $id,
            'username' => $validated['username'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'preference' => $validated['preference'],
            'keywords' => $validated['keywords'],
            'types' => $validated['types'],
            'registered_at' => Carbon::now()->toDateTimeString(),
            'last_login_at' => null,
        ];

        $dataset = $bigQuery->dataset($datasetId);
        $table = $dataset->table($tableId);
        $insertResponse = $table->insertRows([['data' => $data]]);

        if (!$insertResponse->isSuccessful()) {
            foreach ($insertResponse->failedRows() as $failedRow) {
                error_log('Failed Row: ' . json_encode($failedRow));
            }
            return response()->json(['error' => 'Failed to insert data into BigQuery'], 500);
        }

        return redirect()->route('login')->with('success', 'Registrasi berhasil! Silakan login.');
    } catch (Exception $e) {
        return response()->json([
            'error' => $e->getMessage(),
        ], 500);
    }
}

    public function loginUser(Request $request)
{
    try {
        $projectId = 'smart-trip-project'; 
        $datasetId = 'users_dataset'; 
        $tableId = 'users'; 

        $bigQuery = new BigQueryClient(['projectId' => $projectId]);

        // Validasi input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        // Query untuk mengambil data user
        $query = "
            SELECT 
                email, 
                password 
            FROM $projectId.$datasetId.$tableId
            WHERE email = '$email'
            LIMIT 1
        ";
        $queryJobConfig = $bigQuery->query($query);
        $queryJob = $bigQuery->runQuery($queryJobConfig);

        // Cek data user
        $user = null;
        foreach ($queryJob as $row) {
            $user = [
                'email' => $row['email'],
                'password' => $row['password'],
            ];
        }

        if (!$user) {
            return redirect()->back()->with('error', 'Email tidak ditemukan.');
        }

        // Validasi password
        if (!Hash::check($password, $user['password'])) {
            return redirect()->back()->with('error', 'Password salah.');
        }

        // Login berhasil
        return redirect('/dashboard')->with('success', 'Login berhasil!');
    } catch (Exception $e) {
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

}