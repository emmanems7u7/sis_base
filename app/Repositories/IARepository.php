<?php

namespace App\Repositories;

use App\Interfaces\IAInterface;
use App\Models\ChatMensaje;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Http;
use App\Models\IaUsage;
use App\Models\Token;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;
class IARepository implements IAInterface
{


    public function __construct()
    {

    }


    public function registrarConsumo($respuestaIA, $tipoConsulta)
    {
        // Obtener total de tokens usados
        $tokens = $respuestaIA->json('usage.total_tokens', 0);

        // Registrar en BD
        IaUsage::create([
            'usuario' => Auth::id() ?? null,
            'tipo_consulta' => $tipoConsulta,
            'tokens_usados' => $tokens,
        ]);

        return $tokens;
    }

    /*
        public function consultarIA($mensajeActual, $instruccion, $maxTokens, $temperature, $tipoConsulta = 'general', $anonId = null)
        {
            try {
                // 1. Revisar si ya existe respuesta similar en BD
                $respuestaExistente = ChatMensaje::where('role', 'assistant')
                    ->where('tipo_consulta', $tipoConsulta)
                    ->where('contenido', 'like', "%{$mensajeActual}%") // puedes mejorar con fulltext o similitud
                    ->latest()
                    ->first();

                if ($respuestaExistente) {
                    // Devolver respuesta existente sin llamar a IA
                    return response()->json([
                        'respuesta' => $respuestaExistente->contenido,
                        'tokens_usados' => 0,
                        'token_usado' => null,
                        'mensaje' => 'Respuesta tomada de la base de datos'
                    ]);
                }

                // 2. Construir array de mensajes basado en historial del usuario 
                $historial = ChatMensaje::where('tipo_consulta', 'servicios')
                    ->where('anon_id', $anonId)
                    ->whereDate('created_at', Carbon::today())
                    ->orderBy('created_at')
                    ->get(['role', 'contenido'])
                    ->toArray();


                $mensajes = [];
                foreach ($historial as $h) {
                    $mensajes[] = [
                        'role' => $h['role'],
                        'content' => $h['contenido']
                    ];
                }

                // Agregar mensaje del sistema y del usuario
                $mensajes[] = ['role' => 'system', 'content' => $instruccion];
                $mensajes[] = ['role' => 'user', 'content' => $mensajeActual];

                // 3. Obtener tokens activos
                $tokens = Token::activos()->pluck('token');
                if ($tokens->isEmpty()) {
                    return response()->json(['error' => 'No hay tokens disponibles'], 500);
                }

                $contenido = null;
                $tokensUsados = 0;
                $tokenUsado = null;

                $maxIntentos = 5;
                $espera = 1; // segundos

                for ($intento = 1; $intento <= $maxIntentos; $intento++) {
                    foreach ($tokens as $tk) {
                        try {
                            $respuesta = Http::withHeaders([
                                'Authorization' => 'Bearer ' . $tk,
                                'Content-Type' => 'application/json',
                            ])->timeout(15)->post('https://api.groq.com/openai/v1/chat/completions', [
                                        'model' => 'llama3-70b-8192',
                                        'messages' => $mensajes,
                                        'max_tokens' => $maxTokens,
                                        'temperature' => $temperature,
                                    ]);

                            if ($respuesta->successful()) {
                                $contenido = trim($respuesta->json('choices.0.message.content') ?? '');
                                $tokensUsados = $respuesta->json('usage.total_tokens', 0);
                                $tokenUsado = $tk;
                                break 2; // Salir de todos los bucles
                            }

                            if ($respuesta->status() == 429) {
                                Log::warning("Token agotado: {$tk}");
                                continue; // probar siguiente token
                            }

                        } catch (\Exception $e) {
                            Log::error("Error con token {$tk}: " . $e->getMessage());
                            continue;
                        }
                    }

                    // Si llegamos aquí, todos los tokens están agotados
                    sleep($espera); // espera antes de reintentar
                }


                if (is_null($contenido)) {
                    return response()->json(['error' => 'Todos los tokens agotados o inválidos'], 500);
                }

                // 4. Registrar consumo de IA
                IaUsage::create([
                    'usuario' => $anonId ?? null,
                    'tipo_consulta' => $tipoConsulta,
                    'tokens_usados' => $tokensUsados,
                ]);

                // 5. Guardar mensajes en BD 
                ChatMensaje::create([
                    'anon_id' => $anonId,
                    'role' => 'user',
                    'contenido' => $mensajeActual,
                    'tipo_consulta' => $tipoConsulta
                ]);

                ChatMensaje::create([
                    'anon_id' => $anonId,
                    'role' => 'assistant',
                    'contenido' => $contenido,
                    'tipo_consulta' => $tipoConsulta
                ]);

                return response()->json([
                    'respuesta' => $contenido,
                    'tokens_usados' => $tokensUsados,
                ]);

            } catch (\Exception $e) {
                Log::error('Excepción al conectar con Groq', ['exception' => $e->getMessage()]);
                return response()->json(['error' => 'Servicio de IA no disponible'], 503);
            }
        }
    */

    public function consultarIA($mensajeActual, $instruccion, $maxTokens, $temperature, $tipoConsulta = 'general', $anonId = null)
    {
        Log::info('Iniciando consulta IA', ['mensaje' => $mensajeActual, 'instruccion' => $instruccion, 'anonId' => $anonId]);

        try {
            // 1. Revisar si ya existe respuesta similar en BD
            $respuestaExistente = ChatMensaje::where('role', 'assistant')
                ->where('tipo_consulta', $tipoConsulta)
                ->where('contenido', 'like', "%{$mensajeActual}%")
                ->latest()
                ->first();

            if ($respuestaExistente) {
                return response()->json([
                    'respuesta' => $respuestaExistente->contenido,
                    'tokens_usados' => 0,
                    'token_usado' => null,
                    'mensaje' => 'Respuesta tomada de la base de datos'
                ]);
            }

            // 2. Historial de mensajes del usuario
            $historial = ChatMensaje::where('tipo_consulta', $tipoConsulta)
                ->where('anon_id', $anonId)
                ->whereDate('created_at', Carbon::today())
                ->orderBy('created_at')
                ->get(['role', 'contenido'])
                ->toArray();

            Log::info('Historial de mensajes obtenido', ['historial' => $historial]);

            $mensajes = [];
            foreach ($historial as $h) {
                $mensajes[] = [
                    'role' => $h['role'],
                    'content' => $h['contenido']
                ];
            }

            $mensajes[] = ['role' => 'system', 'content' => $instruccion];
            $mensajes[] = ['role' => 'user', 'content' => $mensajeActual];

            Log::info('Mensajes preparados para enviar a IA', ['mensajes' => $mensajes]);

            // 3. Tokens disponibles
            $tokens = Token::activos()->pluck('token');
            if ($tokens->isEmpty()) {
                return response()->json(['error' => 'No hay tokens disponibles'], 500);
            }

            $contenido = null;
            $tokensUsados = 0;
            $tokenUsado = null;

            $maxIntentos = 5;
            $espera = 1; // segundos

            for ($intento = 1; $intento <= $maxIntentos; $intento++) {
                Log::info("Intento #{$intento} de conexión con la IA");

                foreach ($tokens as $tk) {
                    Log::info("Usando token: {$tk}");

                    $payload = [
                        'model' => 'llama-3.1-8b-instant',
                        'messages' => $mensajes,
                        'max_tokens' => $maxTokens,
                        'temperature' => $temperature,
                    ];

                    Log::info('Payload enviado a IA', ['payload' => $payload]);

                    try {
                        $respuesta = Http::withHeaders([
                            'Authorization' => 'Bearer ' . $tk,
                            'Content-Type' => 'application/json',
                        ])->timeout(15)->post('https://api.groq.com/openai/v1/chat/completions', $payload);

                        Log::info('Respuesta HTTP recibida', ['status' => $respuesta->status(), 'body' => $respuesta->body()]);

                        if ($respuesta->successful()) {
                            $contenido = trim($respuesta->json('choices.0.message.content') ?? '');
                            $tokensUsados = $respuesta->json('usage.total_tokens', 0);
                            $tokenUsado = $tk;
                            Log::info('Respuesta obtenida de IA', ['contenido' => $contenido, 'tokensUsados' => $tokensUsados]);
                            break 2;
                        }

                        if ($respuesta->status() == 429) {
                            Log::warning("Token agotado: {$tk}");
                            continue;
                        }

                    } catch (\Exception $e) {
                        Log::error("Error con token {$tk}: " . $e->getMessage());
                        continue;
                    }
                }

                Log::info("Todos los tokens intentados para este intento, esperando {$espera} segundos...");
                sleep($espera);
            }

            if (is_null($contenido)) {
                Log::error('No se obtuvo respuesta de la IA después de ' . $maxIntentos . ' intentos');
                return response()->json(['error' => 'Todos los tokens agotados o inválidos'], 500);
            }

            // 4. Registrar consumo de IA
            IaUsage::create([
                'usuario' => $anonId ?? null,
                'tipo_consulta' => $tipoConsulta,
                'tokens_usados' => $tokensUsados,
            ]);

            // 5. Guardar mensajes en BD
            ChatMensaje::create([
                'anon_id' => $anonId,
                'role' => 'user',
                'contenido' => $mensajeActual,
                'tipo_consulta' => $tipoConsulta
            ]);

            ChatMensaje::create([
                'anon_id' => $anonId,
                'role' => 'assistant',
                'contenido' => $contenido,
                'tipo_consulta' => $tipoConsulta
            ]);

            return response()->json([
                'respuesta' => $contenido,
                'tokens_usados' => $tokensUsados,
                'token_usado' => $tokenUsado
            ]);

        } catch (\Exception $e) {
            Log::error('Excepción al conectar con Groq', ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Servicio de IA no disponible'], 503);
        }
    }

}
