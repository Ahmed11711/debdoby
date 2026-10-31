<?php

namespace App\Http\Controllers\Api\GiftGive;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestApi\Voice\StoreVoiceMessageRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class VoiceMessageController extends Controller
{

    public function index(): JsonResponse
    {
        $voices = auth('api')->user()
            ->voiceMessages()
            ->with('giftGiver:id,name')
            ->latest('id')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $voices,
        ]);
    }




    public function store(StoreVoiceMessageRequest $request): JsonResponse
    {
        $path = 'voices/' . Str::uuid() . '.mp3';
        $out  = Storage::disk('public')->path($path);
        File::ensureDirectoryExists(dirname($out));

        $process = new Process([
            'ffmpeg',
            '-y',
            '-i',
            $request->file('voice')->getRealPath(),
            '-ac',
            '1',
            '-ar',
            '22050',
            '-b:a',
            '32k',
            $out,
        ]);
        $process->run();

        if (! $process->isSuccessful()) {
            return response()->json(['status' => false, 'message' => 'Conversion failed'], 422);
        }

        $voice = auth('api')->user()->voiceMessages()->create([
            'path'          => $path,
            'duration'      => $request->duration,
            'gift_giver_id' => $request->gift_giver_id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Voice uploaded',
            'data'    => $voice,
        ], 201);
    }
}
