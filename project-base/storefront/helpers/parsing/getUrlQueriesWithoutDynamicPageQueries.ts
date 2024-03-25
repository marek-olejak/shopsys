import { UrlQueries } from 'hooks/useQueryParams';
import { FriendlyPagesDestinations } from 'types/friendlyUrl';

export const getUrlQueriesWithoutDynamicPageQueries = (queries: UrlQueries) => {
    const filteredQueries = { ...queries };

    const friendlyPageDynamicSegments = Object.values(FriendlyPagesDestinations).map(
        (pagePath) => pagePath.match(/\[(\w+)\]/)?.[1],
    );

    (Object.keys(filteredQueries) as Array<keyof typeof filteredQueries>).forEach((key) => {
        if (friendlyPageDynamicSegments.includes(key)) {
            delete filteredQueries[key];
        }
    });

    return filteredQueries;
};
