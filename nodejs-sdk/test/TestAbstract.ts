import CoreServices from '@orchesty/nodejs-sdk/dist/lib/DIContainer/CoreServices';
import CurlSender from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/CurlSender';
import DIContainer from '@orchesty/nodejs-sdk/dist/lib/DIContainer/Container';
import Metrics from '@orchesty/nodejs-sdk/dist/lib/Metrics/Metrics';
import MongoDbClient from '@orchesty/nodejs-sdk/dist/lib/Storage/Mongodb/Client';
import { container as c } from '@orchesty/nodejs-sdk';
import p from '../src';
import { OAuth2Provider } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Provider/OAuth2/OAuth2Provider';

/* eslint-disable import/no-mutable-exports */
export let container: DIContainer;
export let db: MongoDbClient;
export let sender: CurlSender;
export let oauth2Provider: OAuth2Provider;
/* eslint-enable import/no-mutable-exports */

export async function prepare(): Promise<void> {
    await p();
    container = c;
    db = container.get(CoreServices.MONGO);
    sender = container.get(CoreServices.CURL);
    oauth2Provider = container.get(CoreServices.OAUTH2_PROVIDER);
}

export async function closeConnection(): Promise<void> {
    await db.down();
    await (container.get(CoreServices.METRICS) as Metrics).close();
}

export async function dropCollection(collection: string) {
    const database = await db.db();
    try {
        await database.dropCollection(collection);
    } catch {
        // ...
    }
}
