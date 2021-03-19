<?php

namespace Zvonchuk\Elastic\Driver\Query;

class QueryBuilders
{
    public static function boolQuery(): BoolQueryBuilder
    {
        return new BoolQueryBuilder();
    }

    public static function termQuery($field, $value): TermQueryBuilder
    {
        return new TermQueryBuilder($field, $value);
    }

    public static function termsQuery($field, $value): TermsQueryBuilder
    {
        return new TermsQueryBuilder($field, $value);
    }

    public static function matchQuery($field, $value): MatchQueryBuilder
    {
        return new MatchQueryBuilder($field, $value);
    }

    public static function matchPhraseQuery($field, $value): MatchPhraseQueryBuilder
    {
        return new MatchPhraseQueryBuilder($field, $value);
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