<?php

namespace Zvonchuk\Elastic\Query;


class QueryBuilders
{
    public static function boolQuery(): BoolQueryBuilder
    {
        return new BoolQueryBuilder();
    }
    
    public static function matchAllQuery() :MatchAllQueryBuilder
    {
        return new MatchAllQueryBuilder();
    }

    public static function GeoDistanceQuery(string $field): GeoDistanceQueryBuilder
    {
        return new GeoDistanceQueryBuilder($field);
    }

    public static function geoBoundingBoxQuery(string $field): GeoBoundingBoxQueryBuilder
    {
        return new GeoBoundingBoxQueryBuilder($field);
    }

    public static function termQuery(string $field, $value): TermQueryBuilder
    {
        return new TermQueryBuilder($field, $value);
    }

    public static function termsQuery(string $field, array $value): TermsQueryBuilder
    {
        return new TermsQueryBuilder($field, $value);
    }

    public static function matchQuery(string $field, $value): MatchQueryBuilder
    {
        return new MatchQueryBuilder($field, $value);
    }

    public static function matchPhraseQuery(string $field, $value): MatchPhraseQueryBuilder
    {
        return new MatchPhraseQueryBuilder($field, $value);
    }

    public static function matchPhrasePrefixQuery(string $field, $value): MatchPhrasePrefixQueryBuilder
    {
        return new MatchPhrasePrefixQueryBuilder($field, $value);
    }

    public static function rangeQuery(string $field): RangeQueryBuilder
    {
        return new RangeQueryBuilder($field);
    }

    public static function existsQuery(string $field): ExistsQueryBuilder
    {
        return new ExistsQueryBuilder($field);
    }
}