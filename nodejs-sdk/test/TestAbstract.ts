import { container as c } from '@orchesty/nodejs-sdk';
import { OAuth2Provider } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Provider/OAuth2/OAuth2Provider';
import DIContainer from '@orchesty/nodejs-sdk/dist/lib/DIContainer/Container';
import DbClient from '@orchesty/nodejs-sdk/dist/lib/Storage/Database/Client';
import CurlSender from '@orchesty/nodejs-sdk/dist/lib/Transport/Curl/CurlSender';
import p from '../src';

/* eslint-disable import/no-mutable-exports */
export let container: DIContainer;
export let db: DbClient;
export let sender: CurlSender;
export let oauth2Provider: OAuth2Provider;
/* eslint-enable import/no-mutable-exports */

export function prepare(): void {
    p();
    container = c;
    db = container.get(DbClient);
    sender = container.get(CurlSender);
    oauth2Provider = container.get(OAuth2Provider);
}
