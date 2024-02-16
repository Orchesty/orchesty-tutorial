import CoreFormsEnum from '@orchesty/nodejs-sdk/dist/lib/Application/Base/CoreFormsEnum';
import {
    ApplicationInstall,
    IApplicationSettings,
} from '@orchesty/nodejs-sdk/dist/lib/Application/Database/ApplicationInstall';
import { ACCESS_TOKEN } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Provider/OAuth2/OAuth2Provider';
import { TOKEN } from '@orchesty/nodejs-sdk/dist/lib/Authorization/Type/Basic/ABasicApplication';
import { HttpMethods } from '@orchesty/nodejs-sdk/dist/lib/Transport/HttpMethods';
import { mockOnce } from '@orchesty/nodejs-sdk/dist/test/MockServer';
import { devIp } from '../.jest/testEnvs';
import { NAME as GitHubApplicationName } from '../src/GitHubApplication';
import { NAME as HubSpotApplicationName } from '../src/HubSpotApplication';

export function appInstall(
    name: string,
    user: string,
    settings: IApplicationSettings,
    nonEncryptedSettings: IApplicationSettings = {},
): ApplicationInstall {
    const app = new ApplicationInstall()
        .setEnabled(true)
        .setName(name)
        .setUser(user)
        .setSettings(settings)
        .setNonEncryptedSettings(nonEncryptedSettings);

    mockOnce([
        {
            request: {
                method: HttpMethods.GET, url: new RegExp(`http:\\/\\/${devIp}:8080\\/document\\/ApplicationInstall.*`),
            },
            response: {
                code: 200,
                body: [{ ...app.toArray(), settings }],
            },
        }]);

    return app;
}

export function hubspotAppInstall(): void {
    appInstall(HubSpotApplicationName, 'user', {
        [CoreFormsEnum.AUTHORIZATION_FORM]: {
            [TOKEN]: {
                [ACCESS_TOKEN]: 'tkn',
            },
        },
    });
}

export function githubAppInstall(): void {
    appInstall(GitHubApplicationName, 'user', {
        [CoreFormsEnum.AUTHORIZATION_FORM]: {
            [TOKEN]: 'abcd',
        },
    });
}
