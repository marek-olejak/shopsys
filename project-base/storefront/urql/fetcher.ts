import { captureException } from '@sentry/nextjs';
import md5 from 'crypto-js/md5';
import { RedisClientType, RedisFunctions, RedisModules, RedisScripts } from 'redis';
import { isClient } from 'utils/isClient';

const FRIENDLY_URL_REGEXP = `@friendlyUrl` as const;
const CACHE_REGEXP = `@redisCache\\(\\s?ttl:\\s?([0-9]*)\\s?\\)` as const;
const QUERY_NAME_REGEXP = `query\\s([A-z]*)(\\([A-z:!0-9$,\\s]*\\))?\\s@redisCache`;
const getRedisPrefixPattern = () => `${process.env.REDIS_PREFIX}:fe:queryCache:`;

const removeDirectiveFromQuery = (
    query: string,
    directiveRegexps: (typeof CACHE_REGEXP | typeof FRIENDLY_URL_REGEXP)[],
) => {
    let cleanedQuery = query;
    for (const directiveRegexp of directiveRegexps) {
        cleanedQuery = cleanedQuery.replace(new RegExp(directiveRegexp), '');
    }

    return cleanedQuery;
};

const createInit = (init?: RequestInit | undefined) => ({
    ...init,
    body:
        typeof init?.body === 'string'
            ? removeDirectiveFromQuery(init.body, [CACHE_REGEXP, FRIENDLY_URL_REGEXP])
            : init?.body,
});

export const fetcher =
    (redisClient: RedisClientType<RedisModules, RedisFunctions, RedisScripts> | undefined) =>
    async (input: URL | RequestInfo, init?: RequestInit | undefined): Promise<Response> => {
        if (!isClient && !redisClient) {
            captureException(
                'Redis client was missing on server. This will cause the Redis cache to not work properly.',
            );
        }

        if (isClient || !init || process.env.GRAPHQL_REDIS_CACHE === '0' || !redisClient) {
            return fetch(input, createInit(init));
        }

        try {
            if (typeof init.body !== 'string' || !init.body.match(CACHE_REGEXP)) {
                return fetch(input, createInit(init));
            }

            const [, rawTtl] = init.body.match(CACHE_REGEXP) as string[];
            const ttl = parseInt(rawTtl, 10);

            if (ttl <= 0) {
                return fetch(input, createInit(init));
            }

            const body = removeDirectiveFromQuery(init.body, [CACHE_REGEXP, FRIENDLY_URL_REGEXP]);
            const host = (init.headers ? new Headers(init.headers) : new Headers()).get('OriginalHost');
            const [, queryName] = init.body.match(QUERY_NAME_REGEXP) ?? [];
            const hash = `${getRedisPrefixPattern()}${queryName}:${host}:${md5(body).toString().substring(0, 7)}`;

            const fromCache = await redisClient.get(hash);

            if (fromCache !== null) {
                const response = new Response(JSON.stringify({ data: JSON.parse(fromCache) }), {
                    statusText: 'OK',
                    status: 200,
                    headers: { 'Content-Type': 'application/json' },
                });
                return Promise.resolve(response);
            }

            const result = await fetch(input, {
                ...init,
                body,
            });

            const res = await result.json();

            if (res.data !== undefined) {
                await redisClient.set(hash, JSON.stringify(res.data), { EX: ttl });
            }
            return Promise.resolve(
                new Response(JSON.stringify(res), {
                    statusText: 'OK',
                    status: 200,
                    headers: { 'Content-Type': 'application/json' },
                }),
            );
        } catch (e) {
            captureException(e);

            return fetch(input, createInit(init));
        }
    };
