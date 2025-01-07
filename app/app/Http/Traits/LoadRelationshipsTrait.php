<?php

namespace App\Http\Traits;

use Illuminate\Contracts\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Model;

trait LoadRelationshipsTrait
{

    /**
     *@param string queryParamKey : default value is 'include';
     *@param ?array relations : default is null
     *                          this param must not be null -> either should be passed as argument or class field;
     *@return QueryBuilder|EloquentBuilder|Model| -> successfully return modified query
     *@return array if  there is an invalid relationship requested. -> error
     */

    public function loadRelationships(QueryBuilder|EloquentBuilder|Model $query, ?array $relations = null, string $queryParamKey = 'include'): QueryBuilder|EloquentBuilder|Model|array
    {

        $relations = $relations ?? $this->relations;
        assert($relations);

        $processedRequestedRelations =   $this->processRequestedRelations($relations, request()->query($queryParamKey));

        if ($processedRequestedRelations["unValidRelations"])
            return [
                'message' => "Invalid value for '{$queryParamKey}' Param.",
                'valid_values' => implode(',', $relations),
            ];


        foreach ($processedRequestedRelations["relationsToLoad"] as $relationToLoad)
            $query->when(true, fn($q) => $query instanceof Model ? $query->load($relationToLoad) : $q->with($relationToLoad));

        return $query;
    }

    protected function processRequestedRelations(array $validRelations, ?string $includeParam = null): array
    {

        $response = ['relationsToLoad' => [], 'unValidRelations' => []];

        if (!$includeParam)
            return $response;

        $requestedRelations = array_unique(explode(',', $includeParam));

        foreach ($requestedRelations  as $requestedRelation)
            if (in_array($requestedRelation, $validRelations))
                array_push($response['relationsToLoad'], $requestedRelation);
            else
                array_push($response['unValidRelations'], $requestedRelation);

        return $response;
    }
}
