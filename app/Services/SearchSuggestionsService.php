<?php

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SearchSuggestionsService
{
    private const SUGGESTIONS_CACHE_KEY = 'search_suggestions';
    private const SUGGESTIONS_CACHE_TTL = 3600; // 1 hora
    private const MIN_SEARCH_LENGTH = 2; // Mínimo 2 caracteres para sugerir
    private const MAX_SUGGESTIONS = 10;

    /**
     * Registra uma busca para análise de sugestões
     */
    public function registerSearch(string $term): void
    {
        if (strlen($term) < self::MIN_SEARCH_LENGTH) {
            return;
        }

        try {
            // Incrementa contador da busca
            $this->incrementSearchCount($term);
            
            // Atualiza relacionamentos
            $this->updateSearchRelationships($term);
            
            // Limpa cache de sugestões para forçar atualização
            $this->clearSuggestionsCache();
            
        } catch (\Exception $e) {
            Log::error('Erro ao registrar busca para sugestões', [
                'term' => $term,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Obtém sugestões baseadas em dados reais do banco (descrições, marcas, categorias)
     * Só retorna palavras que existem em produtos reais
     */
    public function getSuggestions(string $term, int $limit = self::MAX_SUGGESTIONS): array
    {
        if (strlen($term) < 1) {
            return [];
        }

        try {
            // Tenta buscar do cache primeiro
            $cacheKey = self::SUGGESTIONS_CACHE_KEY . ':' . strtolower($term);
            $cached = Redis::get($cacheKey);
            
            if ($cached) {
                return json_decode($cached, true);
            }

            // Busca sugestões do banco de dados (produtos reais)
            $suggestions = $this->generateSuggestionsFromDatabase($term, $limit);
            
            // Cacheia resultado
            Redis::setex($cacheKey, self::SUGGESTIONS_CACHE_TTL, json_encode($suggestions));
            
            return $suggestions;
            
        } catch (\Exception $e) {
            Log::error('Erro ao buscar sugestões', [
                'term' => $term,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Incrementa contador de uma busca
     */
    private function incrementSearchCount(string $term): void
    {
        $key = 'search_count:' . strtolower($term);
        Redis::zincrby('search_counts', $key, 1);
        Redis::expire('search_counts', 86400); // 24 horas
    }

    /**
     * Atualiza relacionamentos entre termos
     */
    private function updateSearchRelationships(string $term): void
    {
        $term = strtolower($term);
        $words = $this->extractWords($term);
        
        foreach ($words as $word) {
            if (strlen($word) < 2) continue;
            
            // Relaciona com outras palavras da mesma busca
            foreach ($words as $relatedWord) {
                if ($word === $relatedWord) continue;
                
                $relKey = 'search_relations:' . $word;
                Redis::zadd($relKey, time(), $relatedWord);
                Redis::expire($relKey, 86400); // 24 horas
                
                // Limita número de relações
                Redis::zremrangebyrank($relKey, 0, -100);
            }
        }
    }

    /**
     * Gera sugestões baseadas em dados REAIS do banco (produtos, marcas, categorias)
     * Só retorna termos que existem em produtos ativos
     */
    private function generateSuggestionsFromDatabase(string $term, int $limit): array
    {
        $term = strtolower($term);
        $suggestions = [];
        
        // 1. Busca descrições de produtos que começam com o termo
        $productSuggestions = $this->getProductSuggestions($term, $limit / 2);
        $suggestions = array_merge($suggestions, $productSuggestions);
        
        // 2. Busca marcas que começam com o termo
        $brandSuggestions = $this->getBrandSuggestions($term, $limit / 3);
        $suggestions = array_merge($suggestions, $brandSuggestions);
        
        // 3. Busca modelos que começam com o termo
        $modelSuggestions = $this->getModelSuggestions($term, $limit / 3);
        $suggestions = array_merge($suggestions, $modelSuggestions);
        
        // Remove duplicados e limita
        $suggestions = array_unique($suggestions, SORT_REGULAR);
        $suggestions = array_slice($suggestions, 0, $limit);
        
        Log::info('Sugestões geradas do banco', [
            'term' => $term,
            'suggestions' => $suggestions,
            'products' => count($productSuggestions),
            'brands' => count($brandSuggestions),
            'models' => count($modelSuggestions)
        ]);
        
        return $suggestions;
    }

    /**
     * Busca sugestões de descrições de produtos
     * Retorna o nome/descrição COMPLETO que contém o termo buscado
     */
    private function getProductSuggestions(string $term, int $limit): array
    {
        $termLower = strtolower($term);
        
        // Busca produtos onde a descrição contém o termo (em qualquer parte)
        $products = \App\Models\Product::where('is_active', true)
            ->where(function ($query) use ($termLower) {
                $query->whereRaw("LOWER(description) LIKE LOWER(?)", ['%' . $termLower . '%'])
                      ->orWhereRaw("LOWER(barcode) LIKE LOWER(?)", ['%' . $termLower . '%']);
            })
            ->select('description', 'barcode', 'brand', 'model')
            ->distinct()
            ->limit($limit * 2)
            ->get();
        
        $suggestions = [];
        $addedTerms = []; // Evita duplicados
        
        foreach ($products as $product) {
            // Prioridade 1: Descrição completa (se contiver o termo)
            if (!empty($product->description)) {
                $descLower = strtolower($product->description);
                if (str_contains($descLower, $termLower)) {
                    $displayText = $this->formatSuggestionText($product->description, $termLower);
                    if (!in_array($displayText, $addedTerms) && strlen($displayText) >= 3) {
                        $suggestions[] = [
                            'term' => $displayText,
                            'score' => 3, // Alta prioridade
                            'type' => 'produto'
                        ];
                        $addedTerms[] = $displayText;
                    }
                }
            }
            
            // Prioridade 2: Código de barras (se contiver o termo)
            if (!empty($product->barcode) && str_contains(strtolower($product->barcode), $termLower)) {
                $displayText = $this->formatSuggestionText($product->barcode, $termLower);
                if (!in_array($displayText, $addedTerms) && strlen($displayText) >= 3) {
                    $suggestions[] = [
                        'term' => $displayText,
                        'score' => 2,
                        'type' => 'produto'
                    ];
                    $addedTerms[] = $displayText;
                }
            }
        }
        
        // Ordena por score (prioridade)
        usort($suggestions, fn($a, $b) => $b['score'] <=> $a['score']);
        
        return array_slice($suggestions, 0, $limit);
    }

    /**
     * Formata o texto da sugestão, truncando se necessário
     * Destaca onde o termo aparece no texto
     */
    private function formatSuggestionText(string $text, string $term): string
    {
        $text = trim($text);
        $termLower = strtolower($term);
        $textLower = strtolower($text);
        
        // Encontra a posição do termo no texto
        $pos = strpos($textLower, $termLower);
        
        if ($pos === false) {
            // Se não encontrou exato, retorna os primeiros 50 caracteres
            return strlen($text) > 50 ? substr($text, 0, 50) . '...' : $text;
        }
        
        // Pega contexto ao redor do termo (30 chars antes e depois)
        $start = max(0, $pos - 30);
        $length = min(strlen($text) - $start, strlen($term) + 60);
        
        $result = substr($text, $start, $length);
        
        // Adiciona elipses se necessário
        if ($start > 0) {
            $result = '...' . $result;
        }
        if ($start + $length < strlen($text)) {
            $result = $result . '...';
        }
        
        return trim($result);
    }

    /**
     * Busca sugestões de marcas
     * Busca em qualquer parte da marca e retorna o nome completo
     */
    private function getBrandSuggestions(string $term, int $limit): array
    {
        $termLower = strtolower($term);
        
        $brands = \App\Models\Product::where('is_active', true)
            ->whereNotNull('brand')
            ->whereRaw("LOWER(brand) LIKE LOWER(?)", ['%' . $termLower . '%'])
            ->select('brand')
            ->distinct()
            ->limit($limit)
            ->get();
        
        $suggestions = [];
        $addedBrands = [];
        
        foreach ($brands as $product) {
            $brandLower = strtolower($product->brand);
            // Só adiciona se realmente contém o termo
            if (str_contains($brandLower, $termLower) && !in_array($product->brand, $addedBrands)) {
                $suggestions[] = [
                    'term' => $product->brand,
                    'score' => 1,
                    'type' => 'marca'
                ];
                $addedBrands[] = $product->brand;
            }
        }
        
        return $suggestions;
    }

    /**
     * Busca sugestões de modelos
     * Busca em qualquer parte do modelo e retorna o nome completo
     */
    private function getModelSuggestions(string $term, int $limit): array
    {
        $termLower = strtolower($term);
        
        $models = \App\Models\Product::where('is_active', true)
            ->whereNotNull('model')
            ->whereRaw("LOWER(model) LIKE LOWER(?)", ['%' . $termLower . '%'])
            ->select('model')
            ->distinct()
            ->limit($limit)
            ->get();
        
        $suggestions = [];
        $addedModels = [];
        
        foreach ($models as $product) {
            $modelLower = strtolower($product->model);
            // Só adiciona se realmente contém o termo
            if (str_contains($modelLower, $termLower) && !in_array($product->model, $addedModels)) {
                $suggestions[] = [
                    'term' => $product->model,
                    'score' => 1,
                    'type' => 'modelo'
                ];
                $addedModels[] = $product->model;
            }
        }
        
        return $suggestions;
    }

    /**
     * Extrai palavras de um texto que começam com o termo
     */
    private function extractWordsStartingWith(string $text, string $term): array
    {
        $text = strtolower($text);
        $term = strtolower($term);
        
        // Remove caracteres especiais
        $clean = preg_replace('/[^a-z0-9\s]/', ' ', $text);
        $words = explode(' ', $clean);
        
        $matches = [];
        foreach ($words as $word) {
            $word = trim($word);
            if (strlen($word) >= 3 && str_starts_with($word, $term)) {
                $matches[] = $word;
            }
        }
        
        return array_unique($matches);
    }

    /**
     * Método legado: Gera sugestões baseadas em histórico Redis (mantido para trending)
     */
    private function generateSuggestions(string $term, int $limit): array
    {
        $term = strtolower($term);
        $suggestions = [];
        
        // 1. Busca termos que começam com o termo
        $startsWith = $this->getTermsStartingWith($term, $limit / 2);
        $suggestions = array_merge($suggestions, $startsWith);
        
        // 2. Busca termos populares relacionados
        $related = $this->getRelatedTerms($term, $limit / 2);
        $suggestions = array_merge($suggestions, $related);
        
        // 3. Busca termos similares (correções)
        $similar = $this->getSimilarTerms($term, $limit / 4);
        $suggestions = array_merge($suggestions, $similar);
        
        // Remove duplicados e limita
        $suggestions = array_unique($suggestions, SORT_REGULAR);
        $suggestions = array_slice($suggestions, 0, $limit);
        
        return $suggestions;
    }

    /**
     * Busca termos que começam com o termo
     */
    private function getTermsStartingWith(string $term, int $limit): array
    {
        $pattern = $term . '*';
        $keys = Redis::keys('search_count:' . $pattern);
        
        $terms = [];
        foreach ($keys as $key) {
            $searchTerm = str_replace('search_count:', '', $key);
            if (str_starts_with($searchTerm, $term) && $searchTerm !== $term) {
                $score = Redis::zscore('search_counts', $key);
                $terms[] = [
                    'term' => $searchTerm,
                    'score' => $score,
                    'type' => 'starts_with'
                ];
            }
        }
        
        // Ordena por popularidade
        usort($terms, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($terms, 0, $limit);
    }

    /**
     * Busca termos relacionados
     */
    private function getRelatedTerms(string $term, int $limit): array
    {
        $relatedKeys = Redis::keys('search_relations:' . $term . '*');
        
        $terms = [];
        foreach ($relatedKeys as $key) {
            $relatedTerms = Redis::zrevrange($key, 0, -1);
            
            foreach ($relatedTerms as $relatedTerm) {
                if ($relatedTerm !== $term) {
                    $score = Redis::zscore('search_counts', 'search_count:' . $relatedTerm);
                    $terms[] = [
                        'term' => $relatedTerm,
                        'score' => $score ?: 0,
                        'type' => 'related'
                    ];
                }
            }
        }
        
        // Ordena por popularidade
        usort($terms, function($a, $b) {
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($terms, 0, $limit);
    }

    /**
     * Busca termos similares (para correções)
     */
    private function getSimilarTerms(string $term, int $limit): array
    {
        // Implementa algoritmo de similaridade simples
        $allKeys = Redis::keys('search_count:*');
        $similar = [];
        
        foreach ($allKeys as $key) {
            $candidate = str_replace('search_count:', '', $key);
            $similarity = $this->calculateSimilarity($term, $candidate);
            
            if ($similarity > 0.6 && $similarity < 1.0 && $candidate !== $term) {
                $score = Redis::zscore('search_counts', $key);
                $similar[] = [
                    'term' => $candidate,
                    'score' => $score ?: 0,
                    'similarity' => $similarity,
                    'type' => 'similar'
                ];
            }
        }
        
        // Ordena por similaridade e popularidade
        usort($similar, function($a, $b) {
            if ($a['similarity'] !== $b['similarity']) {
                return $b['similarity'] <=> $a['similarity'];
            }
            return $b['score'] <=> $a['score'];
        });
        
        return array_slice($similar, 0, $limit);
    }

    /**
     * Calcula similaridade entre dois termos
     */
    private function calculateSimilarity(string $term1, string $term2): float
    {
        $term1 = strtolower($term1);
        $term2 = strtolower($term2);
        
        // Similaridade por prefixo
        $commonPrefix = 0;
        $minLength = min(strlen($term1), strlen($term2));
        
        for ($i = 0; $i < $minLength; $i++) {
            if ($term1[$i] === $term2[$i]) {
                $commonPrefix++;
            } else {
                break;
            }
        }
        
        if ($commonPrefix === 0) return 0;
        
        // Coeficiente de similaridade
        $similarity = $commonPrefix / $minLength;
        
        return $similarity;
    }

    /**
     * Extrai palavras de um termo
     */
    private function extractWords(string $term): array
    {
        // Remove caracteres especiais e divide em palavras
        $clean = preg_replace('/[^a-zA-Z0-9\s]/', ' ', $term);
        $words = array_filter(explode(' ', $clean));
        
        return array_unique(array_map('strtolower', $words));
    }

    /**
     * Limpa cache de sugestões
     */
    private function clearSuggestionsCache(): void
    {
        $pattern = self::SUGGESTIONS_CACHE_KEY . ':*';
        $keys = Redis::keys($pattern);
        
        if (!empty($keys)) {
            Redis::del($keys);
        }
    }

    /**
     * Obtém palavras mais pesquisadas (trending)
     */
    public function getTrendingSearches(int $limit = 10): array
    {
        try {
            // Tenta buscar do cache primeiro
            $cacheKey = 'trending_searches:' . $limit;
            $cached = Redis::get($cacheKey);
            
            if ($cached) {
                return json_decode($cached, true);
            }

            // Busca do Redis ordered set de contadores
            $topSearches = Redis::zrevrange('search_counts', 0, $limit - 1, 'WITHSCORES');
            
            $trending = [];
            foreach ($topSearches as $searchTerm) {
                $term = str_replace('search_count:', '', $searchTerm);
                $score = Redis::zscore('search_counts', 'search_count:' . $term);
                
                $trending[] = [
                    'term' => $term,
                    'count' => (int) $score,
                    'trend' => $this->calculateTrend($term)
                ];
            }

            // Cacheia resultado por 15 minutos
            Redis::setex($cacheKey, 900, json_encode($trending));
            
            Log::info('Trending searches gerados', [
                'limit' => $limit,
                'total_found' => count($trending),
                'top_term' => $trending[0]['term'] ?? 'N/A'
            ]);
            
            return $trending;
            
        } catch (\Exception $e) {
            Log::error('Erro ao obter trending searches', [
                'limit' => $limit,
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }

    /**
     * Calcula tendência de crescimento de um termo
     */
    private function calculateTrend(string $term): string
    {
        try {
            // Busca contadores recentes vs antigos
            $recentKey = 'search_recent:' . $term;
            $oldKey = 'search_old:' . $term;
            
            $recentCount = Redis::get($recentKey) ?? 0;
            $oldCount = Redis::get($oldKey) ?? 0;
            
            if ($oldCount == 0) {
                return $recentCount > 0 ? 'up' : 'stable';
            }
            
            $growth = (($recentCount - $oldCount) / $oldCount) * 100;
            
            if ($growth > 20) return 'up';
            if ($growth < -20) return 'down';
            return 'stable';
            
        } catch (\Exception $e) {
            return 'stable';
        }
    }

    /**
     * Obtém estatísticas das buscas
     */
    public function getSearchStats(): array
    {
        try {
            // Top 20 buscas mais populares
            $topSearches = Redis::zrevrange('search_counts', 0, 19, 'WITHSCORES');
            
            $stats = [];
            foreach ($topSearches as $searchTerm) {
                $term = str_replace('search_count:', '', $searchTerm);
                $stats[] = [
                    'term' => $term,
                    'count' => Redis::zscore('search_counts', 'search_count:' . $term)
                ];
            }
            
            return [
                'top_searches' => $stats,
                'total_unique_terms' => Redis::zcard('search_counts'),
                'cache_keys_count' => count(Redis::keys('*'))
            ];
            
        } catch (\Exception $e) {
            Log::error('Erro ao obter estatísticas', [
                'error' => $e->getMessage()
            ]);
            
            return [];
        }
    }
}
