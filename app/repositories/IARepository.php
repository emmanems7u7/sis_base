<?php

namespace App\Repositories;

use App\Interfaces\IAInterface;
use App\Models\Configuracion;
use Illuminate\Support\Facades\Http;
use App\Models\IaUsage;
use App\Models\Token;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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


    public function consultarIA($consulta, $instruccion, $maxTokens, $temperature, $tipoConsulta = 'general')
    {
        try {
            // 1. Obtener tokens activos
            $tokens = Token::activos()->pluck('token');

            if ($tokens->isEmpty()) {
                return response()->json(['error' => 'No hay tokens disponibles'], 500);
            }

            $contenido = null;
            $tokensUsados = 0;
            $tokenUsado = null;

            // 2. Probar con cada token hasta que funcione
            foreach ($tokens as $tk) {
                try {
                    $respuesta = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $tk,
                        'Content-Type' => 'application/json',
                    ])
                        ->timeout(15)
                        ->post('https://api.groq.com/openai/v1/chat/completions', [
                            'model' => 'llama3-70b-8192',
                            'messages' => [
                                ['role' => 'system', 'content' => $instruccion],
                                ['role' => 'user', 'content' => $consulta]
                            ],
                            'max_tokens' => $maxTokens,
                            'temperature' => $temperature,
                        ]);

                    // 3. Si responde bien, guardamos datos y salimos del bucle
                    if ($respuesta->successful()) {
                        $contenido = trim($respuesta->json('choices.0.message.content') ?? '');
                        $tokensUsados = $respuesta->json('usage.total_tokens', 0);
                        $tokenUsado = $tk;
                        break;
                    }

                    // 4. Si da error de límite (ej: 429) → marcamos token como agotado
                    if ($respuesta->status() == 429) {
                        Token::where('token', $tk)->update(['estado' => 0]);
                    }

                } catch (\Exception $e) {
                    // Cualquier excepción → desactivar token y seguir con otro
                    Token::where('token', $tk)->update(['estado' => 0]);
                }
            }

            // 5. Si ningún token funcionó
            if (is_null($contenido)) {
                return response()->json(['error' => 'Todos los tokens agotados o inválidos'], 500);
            }

            // 6. Registrar consumo
            IaUsage::create([
                'usuario' => Auth::id() ?? null,
                'tipo_consulta' => $tipoConsulta,
                'tokens_usados' => $tokensUsados,
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
