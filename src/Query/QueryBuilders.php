<?php

declare(strict_types=1);

namespace Zvonchuk\Elastic\Query;

final class QueryBuilders
{
    public static function boolQuery(): BoolQueryBuilder
    {
        return new BoolQueryBuilder();
    }

    public static function disMaxQuery(): DisMaxQueryBuilder
    {
        return new DisMaxQueryBuilder();
    }

    public static function constantScoreQuery(QueryInterface $filter): ConstantScoreQueryBuilder
    {
        return new ConstantScoreQueryBuilder($filter);
    }

    public static function nestedQuery(string $path, QueryInterface $query): NestedQueryBuilder
    {
        return new NestedQueryBuilder($path, $query);
    }

    public static function matchAllQuery(): MatchAllQueryBuilder
    {
        return new MatchAllQueryBuilder();
    }

    public static function geoDistanceQuery(string $field): GeoDistanceQueryBuilder
    {
        return new GeoDistanceQueryBuilder($field);
    }

    public static function geoBoundingBoxQuery(string $field): GeoBoundingBoxQueryBuilder
    {
        return new GeoBoundingBoxQueryBuilder($field);
    }

    public static function termQuery(string $field, int|float|bool|string $value): TermQueryBuilder
    {
        return new TermQueryBuilder($field, $value);
    }

    /** @param list<int|float|bool|string> $values */
    public static function termsQuery(string $field, array $values): TermsQueryBuilder
    {
        return new TermsQueryBuilder($field, $values);
    }

    public static function matchQuery(string $field, int|float|bool|string $value): MatchQueryBuilder
    {
        return new MatchQueryBuilder($field, $value);
    }

    /** @param list<string> $fields field names, optionally boosted: "title^3" */
    public static function multiMatchQuery(int|float|bool|string $value, array $fields): MultiMatchQueryBuilder
    {
        return new MultiMatchQueryBuilder($value, $fields);
    }

    public static function matchPhraseQuery(string $field, string $value): MatchPhraseQueryBuilder
    {
        return new MatchPhraseQueryBuilder($field, $value);
    }

    public static function matchPhrasePrefixQuery(string $field, string $value): MatchPhrasePrefixQueryBuilder
    {
        return new MatchPhrasePrefixQueryBuilder($field, $value);
    }

    public static function prefixQuery(string $field, string $value): PrefixQueryBuilder
    {
        return new PrefixQueryBuilder($field, $value);
    }

    public static function wildcardQuery(string $field, string $pattern): WildcardQueryBuilder
    {
        return new WildcardQueryBuilder($field, $pattern);
    }

    /** @param list<string> $ids */
    public static function idsQuery(array $ids): IdsQueryBuilder
    {
        return new IdsQueryBuilder($ids);
    }

    public static function fuzzyQuery(string $field, string $value): FuzzyQueryBuilder
    {
        return new FuzzyQueryBuilder($field, $value);
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
