<?php

namespace App\Helpers;

class SanitizerHelper
{
    /**
     * Aplica trim e strip_tags em todos os campos de um array,
     * com exceção dos campos especificados.
     * 
     * @param array $data Dados a serem sanitizados
     * @param array $except Campos que não devem ser sanitizados
     * @return array
     */
    public static function sanitize(array $data, array $except = []): array
    {
        return self::sanitizeArray($data, $except);
    }
    
    /**
     * Sanitiza dados para a tabela SEO, exceto schema_markup e google_tag_manager
     * 
     * @param array $data Dados SEO
     * @return array
     */
    public static function sanitizeSeoData(array $data): array
    {
        return self::sanitize($data, ['schema_markup', 'google_tag_manager']);
    }
    
    /**
     * Função recursiva para sanitizar arrays aninhados
     */
    private static function sanitizeArray(array $data, array $except = []): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                // Se for um array, aplica recursivamente
                $data[$key] = self::sanitizeArray($value, $except);
            } elseif (is_string($value)) {
                // Se for string e não estiver na lista de exceções
                if (!in_array($key, $except)) {
                    $data[$key] = self::sanitizeString($value);
                }
            }
            // Outros tipos (int, float, bool, null) são mantidos como estão
        }
        
        return $data;
    }
    
    /**
     * Sanitiza uma string individual removendo tags e padrões perigosos
     */
    private static function sanitizeString(string $value): string
    {
        // Remove tags HTML completamente (incluindo conteúdo dentro de script/style)
        $cleaned = strip_tags($value);
        
        // Remove espaços em branco no início e fim
        $cleaned = trim($cleaned);
        
        // Remove múltiplos espaços em branco internos
        $cleaned = preg_replace('/\s+/', ' ', $cleaned);
        
        // Remove padrões javascript perigosos que possam ter escapado
        $cleaned = preg_replace('/javascript\s*:/i', '', $cleaned);
        
        // Remove eventos on* perigosos
        $cleaned = preg_replace('/on\w+\s*=/i', '', $cleaned);
        
        // Remove conteúdo de scripts que possa ter escapado
        $cleaned = preg_replace('/alert\s*\([^)]*\)/i', '', $cleaned);
        
        return $cleaned;
    }
}
