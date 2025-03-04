import { useRouter } from 'next/router';
import { FilterOptionsUrlQueryType } from 'types/productFilter';
import { UrlQueries } from 'types/urlQueries';
import { getQueryWithoutSlugTypeParameterFromParsedUrlQuery } from 'utils/parsing/getQueryWithoutSlugTypeParameterFromParsedUrlQuery';
import { FILTER_QUERY_PARAMETER_NAME } from 'utils/queryParamNames';

export const useCurrentFilterQuery = () => {
    const router = useRouter();
    const query = getQueryWithoutSlugTypeParameterFromParsedUrlQuery(router.query) as UrlQueries;
    const filterQueryAsString = query[FILTER_QUERY_PARAMETER_NAME];
    const filterQuery = filterQueryAsString ? (JSON.parse(filterQueryAsString) as FilterOptionsUrlQueryType) : null;

    return filterQuery;
};
